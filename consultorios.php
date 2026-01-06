<?php
// ===============================
// CONEXIÓN BD
// ===============================
$conn = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// ===============================
// CATÁLOGOS CONTROLADOS
// ===============================
$lista_consultorios = [
    "Consultorio 1",
    "Consultorio 2",
    "Consultorio 3"
];

$lista_servicios = [
    "Consulta general",
    "Vacunación",
    "Cirugía",
    "Desparasitación",
    "Control postoperatorio"
];

$lista_ubicaciones = [
    "Primer piso",
    "Segundo piso",
    "Otro"
];

// ===============================
// VETERINARIOS DESDE BD (NOMBRE COMPLETO)
// ===============================
$veterinarios = $conn->query("
    SELECT id_veterinario,
           CONCAT(nombre, ' ', apellido) AS nombre_completo
    FROM veterinarios
    ORDER BY nombre, apellido
");

// ===============================
// VARIABLES
// ===============================
$id_consultorio = "";
$nombre = "";
$servicio = "";
$veterinario_id = "";
$ubicacion = "";
$telefono = "";

// ===============================
// GUARDAR / ACTUALIZAR
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_consultorio  = $_POST["id_consultorio"] ?? "";
    $nombre          = $_POST["nombre"];
    $servicio        = $_POST["servicio"];
    $veterinario_id  = $_POST["veterinario_id"];
    $ubicacion       = $_POST["ubicacion"];
    $telefono        = $_POST["telefono"];

    if ($id_consultorio == "") {

        $stmt = $conn->prepare(
            "INSERT INTO consultorios (nombre, servicio, veterinario_id, ubicacion, telefono)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssiss", $nombre, $servicio, $veterinario_id, $ubicacion, $telefono);
        $stmt->execute();

    } else {

        $stmt = $conn->prepare(
            "UPDATE consultorios
             SET nombre=?, servicio=?, veterinario_id=?, ubicacion=?, telefono=?
             WHERE id_consultorio=?"
        );
        $stmt->bind_param("ssissi", $nombre, $servicio, $veterinario_id, $ubicacion, $telefono, $id_consultorio);
        $stmt->execute();
    }

    header("Location: consultorios.php");
    exit;
}

// ===============================
// EDITAR
// ===============================
if (isset($_GET["editar"])) {
    $stmt = $conn->prepare("SELECT * FROM consultorios WHERE id_consultorio=?");
    $stmt->bind_param("i", $_GET["editar"]);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        $id_consultorio = $row["id_consultorio"];
        $nombre = $row["nombre"];
        $servicio = $row["servicio"];
        $veterinario_id = $row["veterinario_id"];
        $ubicacion = $row["ubicacion"];
        $telefono = $row["telefono"];
    }
}

// ===============================
// ELIMINAR
// ===============================
if (isset($_GET["eliminar"])) {
    $stmt = $conn->prepare("DELETE FROM consultorios WHERE id_consultorio=?");
    $stmt->bind_param("i", $_GET["eliminar"]);
    $stmt->execute();
    header("Location: consultorios.php");
    exit;
}

// ===============================
// LISTADO CON JOIN REAL
// ===============================
$consultorios = $conn->query("
    SELECT c.id_consultorio,
           c.nombre,
           c.servicio,
           CONCAT(v.nombre, ' ', v.apellido) AS veterinario,
           c.ubicacion,
           c.telefono
    FROM consultorios c
    INNER JOIN veterinarios v
        ON c.veterinario_id = v.id_veterinario
    ORDER BY c.id_consultorio DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Consultorios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="stylos.css">
</head>

<body>

<header class="header">
    <div class="header-left">
        <img src="img/logo.png" alt="Logo">
        <span class="header-title">Veterinaria Santiago Barrera</span>
    </div>

    <nav class="header-nav">
        <a href="index.php">Inicio</a>
        <a href="clientes.php">Clientes</a>
        <a href="mascotas.php">Mascotas</a>
        <a href="citas.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php" class="active">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<main class="container my-4">

<div class="title-vet">
    <h4>Gestión de Consultorios</h4>
</div>

<div class="card mb-4 shadow-sm">
<div class="card-body">

<h6 class="mb-3">
<?= $id_consultorio ? "Editar Consultorio" : "Registrar Consultorio"; ?>
</h6>

<form method="POST">
<input type="hidden" name="id_consultorio" value="<?= $id_consultorio ?>">

<div class="row g-3">

<div class="col-md-3">
<label class="form-label">Consultorio</label>
<select name="nombre" class="form-select" required>
<option value="">Seleccione</option>
<?php foreach ($lista_consultorios as $c): ?>
<option value="<?= $c ?>" <?= $nombre == $c ? "selected" : "" ?>>
<?= $c ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3">
<label class="form-label">Servicio</label>
<select name="servicio" class="form-select" required>
<option value="">Seleccione</option>
<?php foreach ($lista_servicios as $s): ?>
<option value="<?= $s ?>" <?= $servicio == $s ? "selected" : "" ?>>
<?= $s ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3">
<label class="form-label">Veterinario</label>
<select name="veterinario_id" class="form-select" required>
<option value="">Seleccione</option>
<?php
$veterinarios->data_seek(0);
while ($v = $veterinarios->fetch_assoc()):
?>
<option value="<?= $v['id_veterinario'] ?>"
<?= $veterinario_id == $v['id_veterinario'] ? "selected" : "" ?>>
<?= $v['nombre_completo'] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-3">
<label class="form-label">Ubicación</label>
<select name="ubicacion" class="form-select">
<?php foreach ($lista_ubicaciones as $u): ?>
<option value="<?= $u ?>" <?= $ubicacion == $u ? "selected" : "" ?>>
<?= $u ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-12">
<label class="form-label">Teléfono</label>
<input type="text" name="telefono" class="form-control" value="<?= $telefono ?>">
</div>

</div>

<div class="mt-4">
<button class="btn btn-success">💾 Guardar</button>
<a href="consultorios.php" class="btn btn-secondary">Cancelar</a>
</div>

</form>
</div>
</div>

<div class="card shadow-sm">
<div class="card-body">
<h6 class="mb-3">📋 Lista de Consultorios</h6>

<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>ID</th>
<th>Consultorio</th>
<th>Servicio</th>
<th>Veterinario</th>
<th>Ubicación</th>
<th>Teléfono</th>
<th class="text-center">Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($c = $consultorios->fetch_assoc()): ?>
<tr>
<td><?= $c["id_consultorio"] ?></td>
<td><?= $c["nombre"] ?></td>
<td><?= $c["servicio"] ?></td>
<td><?= $c["veterinario"] ?></td>
<td><?= $c["ubicacion"] ?></td>
<td><?= $c["telefono"] ?></td>
<td class="text-center">
<a href="?editar=<?= $c["id_consultorio"] ?>" class="btn-action btn-edit">✏️ Editar</a>
<a href="?eliminar=<?= $c["id_consultorio"] ?>" class="btn-action btn-delete"
onclick="return confirm('¿Eliminar consultorio?')">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

</main>

<footer class="footer-vet mt-auto">
    <div class="container text-center">
        <p class="fw-semibold mb-1">
            🐾 Veterinaria Santiago Barrera
        </p>
        <p class="text-muted mb-2">
            Cuidado profesional y amor para tus mascotas
        </p>
        <div class="d-flex justify-content-center gap-3 mb-2">
            <span>🟢 WhatsApp: 317 680 1793</span>
            <span>📸 Instagram: @santiagobarreraveterinario</span>
        </div>
        <small class="text-muted">
            © 2025 Veterinaria Santiago Barrera
        </small>
    </div>
</footer>

</body>
</html>
