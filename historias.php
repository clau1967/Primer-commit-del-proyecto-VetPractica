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
$mascota_id = "";
$veterinario_id = "";
$fecha = "";
$motivo = "";
$diagnostico = "";
$tratamiento = "";
$peso = "";
$temperatura = "";
$frecuencia_cardiaca = "";
$frecuencia_respiratoria = "";
$estado_animo = "";
$observaciones = "";
$proximo_control = "";

// ===============================
// GUARDAR / ACTUALIZAR
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST["id"] ?? "";
    $mascota_id = $_POST["mascota_id"];
    $veterinario_id = $_POST["veterinario_id"];
    $fecha = $_POST["fecha"];
    $motivo = $_POST["motivo"];
    $diagnostico = $_POST["diagnostico"];
    $tratamiento = $_POST["tratamiento"];
    $peso = $_POST["peso"] ?: null;
    $temperatura = $_POST["temperatura"] ?: null;
    $frecuencia_cardiaca = $_POST["frecuencia_cardiaca"] ?: null;
    $frecuencia_respiratoria = $_POST["frecuencia_respiratoria"] ?: null;
    $estado_animo = $_POST["estado_animo"];
    $observaciones = $_POST["observaciones"];
    $proximo_control = $_POST["proximo_control"] ?: null;

    if ($id == "") {

        $stmt = $conn->prepare("
            INSERT INTO historias_clinicas
            (mascota_id, veterinario_id, fecha, motivo, diagnostico, tratamiento,
             peso, temperatura, frecuencia_cardiaca, frecuencia_respiratoria,
             estado_animo, observaciones, proximo_control)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "iissssddiiiss",
            $mascota_id, $veterinario_id, $fecha, $motivo, $diagnostico, $tratamiento,
            $peso, $temperatura, $frecuencia_cardiaca, $frecuencia_respiratoria,
            $estado_animo, $observaciones, $proximo_control
        );
        $stmt->execute();

    } else {

        $stmt = $conn->prepare("
            UPDATE historias_clinicas SET
            mascota_id=?, veterinario_id=?, fecha=?, motivo=?, diagnostico=?, tratamiento=?,
            peso=?, temperatura=?, frecuencia_cardiaca=?, frecuencia_respiratoria=?,
            estado_animo=?, observaciones=?, proximo_control=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "iissssddiiissi",
            $mascota_id, $veterinario_id, $fecha, $motivo, $diagnostico, $tratamiento,
            $peso, $temperatura, $frecuencia_cardiaca, $frecuencia_respiratoria,
            $estado_animo, $observaciones, $proximo_control, $id
        );
        $stmt->execute();
    }

    header("Location: historias.php");
    exit;
}

// ===============================
// EDITAR
// ===============================
if (isset($_GET["editar"])) {
    $id = $_GET["editar"];
    $stmt = $conn->prepare("SELECT * FROM historias_clinicas WHERE id=?");
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
    $stmt = $conn->prepare("DELETE FROM historias_clinicas WHERE id=?");
    $stmt->bind_param("i", $_GET["eliminar"]);
    $stmt->execute();
    header("Location: historias.php");
    exit;
}

// ===============================
// LISTADOS
// ===============================
$historias = $conn->query("
    SELECT h.*, 
           m.nombre AS mascota,
           c.nombre AS cliente_nombre, 
           c.apellido AS cliente_apellido,
           v.nombre AS vet_nombre, 
           v.apellido AS vet_apellido
    FROM historias_clinicas h
    JOIN mascotas m ON h.mascota_id = m.id_mascota
    JOIN clientes c ON m.id_cliente = c.id_cliente
    JOIN veterinarios v ON h.veterinario_id = v.id_veterinario
    ORDER BY h.id DESC
");

/* 🔹 AJUSTE ÚNICO AQUÍ */
$mascotas = $conn->query("
    SELECT m.id_mascota, m.nombre,
           c.nombre AS cliente_nombre,
           c.apellido AS cliente_apellido
    FROM mascotas m
    JOIN clientes c ON m.id_cliente = c.id_cliente
    ORDER BY c.nombre, m.nombre
");

$veterinarios = $conn->query("SELECT * FROM veterinarios ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historias Clínicas</title>

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
        <a href="historias.php" class="active">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<!-- ===============================
 CONTENIDO
================================ -->
<main class="flex-fill container my-4">

<div class="title-vet">
    <h4>Historias Clínicas</h4>
</div>

<div class="card mb-4 shadow-sm">
<div class="card-body">
<h6 class="mb-3 text-primary">
<?php echo $id ? "Editar Historia Clínica" : "Registrar Historia Clínica"; ?>
</h6>

<form method="POST">
<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="row g-3">

<div class="col-md-4">
<label class="form-label">Mascota</label>
<select name="mascota_id" class="form-control" required>
<option value="">Seleccione</option>
<?php while ($m = $mascotas->fetch_assoc()): ?>
<option value="<?php echo $m["id_mascota"]; ?>" <?php if ($mascota_id == $m["id_mascota"]) echo "selected"; ?>>
<?php echo $m["nombre"] . " - " . $m["cliente_nombre"] . " " . $m["cliente_apellido"]; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Veterinario</label>
<select name="veterinario_id" class="form-control" required>
<option value="">Seleccione</option>
<?php while ($v = $veterinarios->fetch_assoc()): ?>
<option value="<?php echo $v["id_veterinario"]; ?>" <?php if ($veterinario_id == $v["id_veterinario"]) echo "selected"; ?>>
<?php echo $v["nombre"] . " " . $v["apellido"]; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Fecha</label>
<input type="date" name="fecha" class="form-control" value="<?php echo $fecha; ?>" required>
</div>

<div class="col-md-6">
<label class="form-label">Motivo</label>
<input type="text" name="motivo" class="form-control" value="<?php echo $motivo; ?>" required>
</div>

<div class="col-md-6">
<label class="form-label">Estado de ánimo</label>
<input type="text" name="estado_animo" class="form-control" value="<?php echo $estado_animo; ?>">
</div>

<div class="col-md-6">
<label class="form-label">Diagnóstico</label>
<textarea name="diagnostico" class="form-control"><?php echo $diagnostico; ?></textarea>
</div>

<div class="col-md-6">
<label class="form-label">Tratamiento</label>
<textarea name="tratamiento" class="form-control"><?php echo $tratamiento; ?></textarea>
</div>

<div class="col-md-3">
<label class="form-label">Peso (kg)</label>
<input type="number" step="0.01" name="peso" class="form-control" value="<?php echo $peso; ?>">
</div>

<div class="col-md-3">
<label class="form-label">Temperatura °C</label>
<input type="number" step="0.1" name="temperatura" class="form-control" value="<?php echo $temperatura; ?>">
</div>

<div class="col-md-3">
<label class="form-label">Frecuencia cardíaca</label>
<input type="number" name="frecuencia_cardiaca" class="form-control" value="<?php echo $frecuencia_cardiaca; ?>">
</div>

<div class="col-md-3">
<label class="form-label">Frecuencia respiratoria</label>
<input type="number" name="frecuencia_respiratoria" class="form-control" value="<?php echo $frecuencia_respiratoria; ?>">
</div>

<div class="col-md-12">
<label class="form-label">Observaciones</label>
<textarea name="observaciones" class="form-control"><?php echo $observaciones; ?></textarea>
</div>

<div class="col-md-4">
<label class="form-label">Próximo control</label>
<input type="date" name="proximo_control" class="form-control" value="<?php echo $proximo_control; ?>">
</div>

</div>

<div class="mt-4">
<button class="btn btn-success">💾 Guardar</button>
<a href="historias.php" class="btn btn-secondary">Cancelar</a>
</div>

</form>
</div>
</div>

<div class="card shadow-sm">
<div class="card-body">
<h6 class="mb-3">📋 Historial Clínico</h6>

<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>ID</th>
<th>Mascota</th>
<th>Cliente</th>
<th>Veterinario</th>
<th>Fecha</th>
<th class="text-center">Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($h = $historias->fetch_assoc()): ?>
<tr>
<td><?php echo $h["id"]; ?></td>
<td><?php echo $h["mascota"]; ?></td>
<td><?php echo $h["cliente_nombre"] . " " . $h["cliente_apellido"]; ?></td>
<td><?php echo $h["vet_nombre"] . " " . $h["vet_apellido"]; ?></td>
<td><?php echo $h["fecha"]; ?></td>
<td class="text-center">
<a href="historias.php?editar=<?php echo $h["id"]; ?>" class="btn-action btn-edit">✏️</a>
<a href="historias.php?eliminar=<?php echo $h["id"]; ?>" class="btn-action btn-delete"
onclick="return confirm('¿Eliminar historia clínica?')">🗑</a>
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
