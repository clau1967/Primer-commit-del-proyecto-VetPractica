<?php
// ===============================
// CONEXIÓN BD
// ===============================
$conn = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// ===============================
// VARIABLES
// ===============================
$id = "";
$historia_clinica_id = "";
$descripcion = "";
$dosis = "";
$indicaciones = "";
$medicamento = "";

// ===============================
// GUARDAR / ACTUALIZAR
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST["id"] ?? "";
    $historia_clinica_id = $_POST["historia_clinica_id"];
    $descripcion = $_POST["descripcion"];
    $dosis = $_POST["dosis"];
    $indicaciones = $_POST["indicaciones"];
    $medicamento = $_POST["medicamento"];

    if ($id == "") {

        $stmt = $conn->prepare("
            INSERT INTO formulas
            (descripcion, dosis, indicaciones, medicamento, historia_clinica_id)
            VALUES (?,?,?,?,?)
        ");
        $stmt->bind_param(
            "ssssi",
            $descripcion, $dosis, $indicaciones, $medicamento, $historia_clinica_id
        );
        $stmt->execute();

    } else {

        $stmt = $conn->prepare("
            UPDATE formulas SET
            descripcion=?, dosis=?, indicaciones=?, medicamento=?, historia_clinica_id=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "ssssii",
            $descripcion, $dosis, $indicaciones, $medicamento, $historia_clinica_id, $id
        );
        $stmt->execute();
    }

    header("Location: formulas.php");
    exit;
}

// ===============================
// EDITAR
// ===============================
if (isset($_GET["editar"])) {
    $id = $_GET["editar"];
    $stmt = $conn->prepare("SELECT * FROM formulas WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        extract($row);
    }
}

// ===============================
// ELIMINAR
// ===============================
if (isset($_GET["eliminar"])) {
    $stmt = $conn->prepare("DELETE FROM formulas WHERE id=?");
    $stmt->bind_param("i", $_GET["eliminar"]);
    $stmt->execute();
    header("Location: formulas.php");
    exit;
}

// ===============================
// LISTADOS
// ===============================
$formulas = $conn->query("
    SELECT f.*,
           h.id AS historia_id,
           h.motivo,
           m.nombre AS mascota,
           c.nombre AS cliente_nombre,
           c.apellido AS cliente_apellido
    FROM formulas f
    JOIN historias_clinicas h ON f.historia_clinica_id = h.id
    JOIN mascotas m ON h.mascota_id = m.id_mascota
    JOIN clientes c ON m.id_cliente = c.id_cliente
    ORDER BY f.id DESC
");

$historias = $conn->query("
    SELECT h.id,
           h.motivo,
           m.nombre AS mascota,
           c.nombre AS cliente_nombre,
           c.apellido AS cliente_apellido
    FROM historias_clinicas h
    JOIN mascotas m ON h.mascota_id = m.id_mascota
    JOIN clientes c ON m.id_cliente = c.id_cliente
    ORDER BY h.id DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Fórmulas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="stylos.css">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- ===============================
 HEADER
================================ -->
<header class="header">
    <div class="header-left">
        <img src="img/logo.png" alt="Veterinaria Santiago Barrera">
        <span class="header-title">Veterinaria Santiago Barrera</span>
    </div>

    <nav class="header-nav">
        <a href="index.php">Inicio</a>
        <a href="clientes.php">Clientes</a>
        <a href="mascotas.php">Mascotas</a>
        <a href="citas.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php" class="active">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<!-- ===============================
 CONTENIDO
================================ -->
<main class="flex-fill container my-4">

<div class="title-vet">
    <h4>Fórmulas Médicas</h4>
</div>

<div class="card mb-4 shadow-sm">
<div class="card-body">
<h6 class="mb-3 text-primary">
<?php echo $id ? "Editar Fórmula" : "Registrar Fórmula"; ?>
</h6>

<form method="POST">
<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="row g-3">

<div class="col-md-12">
<label class="form-label">Historia clínica</label>
<select name="historia_clinica_id" class="form-control" required>
<option value="">Seleccione</option>
<?php while ($h = $historias->fetch_assoc()): ?>
<option value="<?php echo $h["id"]; ?>" <?php if ($historia_clinica_id == $h["id"]) echo "selected"; ?>>
<?php echo $h["cliente_nombre"]." ".$h["cliente_apellido"]." - ".$h["mascota"]." (".$h["motivo"].")"; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Medicamento</label>
<input type="text" name="medicamento" class="form-control" value="<?php echo $medicamento; ?>" required>
</div>

<div class="col-md-6">
<label class="form-label">Dosis</label>
<input type="text" name="dosis" class="form-control" value="<?php echo $dosis; ?>">
</div>

<div class="col-md-12">
<label class="form-label">Descripción</label>
<textarea name="descripcion" class="form-control"><?php echo $descripcion; ?></textarea>
</div>

<div class="col-md-12">
<label class="form-label">Indicaciones</label>
<textarea name="indicaciones" class="form-control"><?php echo $indicaciones; ?></textarea>
</div>

</div>

<div class="mt-4">
<button class="btn btn-success">💾 Guardar</button>
<a href="formulas.php" class="btn btn-secondary">Cancelar</a>
</div>

</form>
</div>
</div>

<div class="card shadow-sm">
<div class="card-body">
<h6 class="mb-3">📋 Lista de Fórmulas</h6>

<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Mascota</th>
<th>Motivo</th>
<th>Medicamento</th>
<th>Dosis</th>
<th class="text-center">Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($f = $formulas->fetch_assoc()): ?>
<tr>
<td><?php echo $f["id"]; ?></td>
<td><?php echo $f["cliente_nombre"]." ".$f["cliente_apellido"]; ?></td>
<td><?php echo $f["mascota"]; ?></td>
<td><?php echo $f["motivo"]; ?></td>
<td><?php echo $f["medicamento"]; ?></td>
<td><?php echo $f["dosis"]; ?></td>
<td class="text-center">
<a href="formulas.php?editar=<?php echo $f["id"]; ?>" class="btn-action btn-edit">✏️</a>
<a href="formulas.php?eliminar=<?php echo $f["id"]; ?>" class="btn-action btn-delete"
onclick="return confirm('¿Eliminar fórmula?')">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

</main>

<!-- ===============================
 FOOTER OFICIAL
================================ -->
<footer class="footer-vet mt-auto">
<div class="container text-center">
<p class="fw-semibold mb-1">🐾 Veterinaria Santiago Barrera</p>
<p class="text-muted mb-2">Cuidado profesional y amor para tus mascotas</p>
<div class="d-flex justify-content-center gap-3 mb-2">
<span>🟢 WhatsApp: 317 680 1793</span>
<span>📸 Instagram: @santiagobarreraveterinario</span>
</div>
<small class="text-muted">© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados</small>
</div>
</footer>

</body>
</html>
