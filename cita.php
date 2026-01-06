<?php
// ===============================
// CONEXIÓN BD
// ===============================
$conn = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

/* =====================================================
   RESPUESTA AJAX (SERVICIOS POR CONSULTORIO)
   ===================================================== */
if (isset($_GET["ajax"]) && $_GET["ajax"] === "servicios") {
    $id_consultorio = intval($_GET["id_consultorio"]);

    $stmt = $conn->prepare(
        "SELECT id_servicio, nombre 
         FROM servicios 
         WHERE id_consultorio = ?"
    );
    $stmt->bind_param("i", $id_consultorio);
    $stmt->execute();
    $result = $stmt->get_result();

    $servicios = [];
    while ($row = $result->fetch_assoc()) {
        $servicios[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode($servicios);
    exit;
}

// ===============================
// VARIABLES
// ===============================
$id_cita = "";
$id_cliente = "";
$id_mascota = "";
$id_veterinario = "";
$id_consultorio = "";
$fecha = "";
$hora = "";
$motivo = "";
$estado = "Pendiente";

// ===============================
// CATÁLOGOS
// ===============================
$estados = ["Pendiente", "Confirmada", "Cancelada", "Finalizada"];

// ===============================
// GUARDAR / ACTUALIZAR
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_cita        = $_POST["id_cita"] ?? "";
    $id_cliente     = $_POST["id_cliente"];
    $id_mascota     = $_POST["id_mascota"];
    $id_veterinario = $_POST["id_veterinario"];
    $id_consultorio = $_POST["id_consultorio"];
    $fecha          = $_POST["fecha"];
    $hora           = $_POST["hora"];
    $motivo         = $_POST["motivo"];
    $estado         = $_POST["estado"];

    if ($id_cita == "") {
        $stmt = $conn->prepare(
            "INSERT INTO citas
            (id_cliente, id_mascota, id_veterinario, id_consultorio, fecha, hora, motivo, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iiiissss",
            $id_cliente, $id_mascota, $id_veterinario, $id_consultorio,
            $fecha, $hora, $motivo, $estado
        );
        $stmt->execute();
    } else {
        $stmt = $conn->prepare(
            "UPDATE citas SET
            id_cliente=?, id_mascota=?, id_veterinario=?, id_consultorio=?,
            fecha=?, hora=?, motivo=?, estado=?
            WHERE id_cita=?"
        );
        $stmt->bind_param(
            "iiiissssi",
            $id_cliente, $id_mascota, $id_veterinario, $id_consultorio,
            $fecha, $hora, $motivo, $estado, $id_cita
        );
        $stmt->execute();
    }

    header("Location: cita.php");
    exit;
}

// ===============================
// EDITAR
// ===============================
if (isset($_GET["editar"])) {
    $stmt = $conn->prepare("SELECT * FROM citas WHERE id_cita=?");
    $stmt->bind_param("i", $_GET["editar"]);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) extract($row);
}

// ===============================
// ELIMINAR
// ===============================
if (isset($_GET["eliminar"])) {
    $stmt = $conn->prepare("DELETE FROM citas WHERE id_cita=?");
    $stmt->bind_param("i", $_GET["eliminar"]);
    $stmt->execute();
    header("Location: cita.php");
    exit;
}

// ===============================
// LISTADOS
// ===============================
$clientes = $conn->query("SELECT id_cliente, nombre, apellido FROM clientes ORDER BY nombre");
$mascotas = $conn->query("SELECT id_mascota, nombre, id_cliente FROM mascotas");
$veterinarios = $conn->query("SELECT id_veterinario, nombre, apellido FROM veterinarios");
$consultorios = $conn->query("SELECT id_consultorio, nombre FROM consultorios");

$citas = $conn->query("
    SELECT c.*,
           cl.nombre AS cliente_nombre, cl.apellido AS cliente_apellido,
           m.nombre AS mascota_nombre,
           v.nombre AS vet_nombre, v.apellido AS vet_apellido,
           co.nombre AS consultorio_nombre
    FROM citas c
    JOIN clientes cl ON c.id_cliente = cl.id_cliente
    JOIN mascotas m ON c.id_mascota = m.id_mascota
    JOIN veterinarios v ON c.id_veterinario = v.id_veterinario
    JOIN consultorios co ON c.id_consultorio = co.id_consultorio
    ORDER BY c.fecha DESC, c.hora DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Citas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="stylos.css">
</head>

<body>

<!-- HEADER -->
<header class="header">
    <div class="header-left">
        <img src="img/logo.png">
        <span class="header-title">Veterinaria Santiago Barrera</span>
    </div>
    <nav class="header-nav">
        <a href="index.php">Inicio</a>
        <a href="clientes.php">Clientes</a>
        <a href="mascotas.php">Mascotas</a>
        <a href="cita.php" class="active">Citas</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="historias.php">Historias</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<main class="container my-4">

<div class="card mb-4 shadow-sm">
<div class="card-body">

<h6 class="mb-3 text-primary"><?= $id_cita ? "Editar Cita" : "Registrar Cita"; ?></h6>

<form method="POST">
<input type="hidden" name="id_cita" value="<?= $id_cita ?>">

<div class="row g-3">

<div class="col-md-4">
<label class="form-label">Cliente</label>
<select name="id_cliente" id="cliente" class="form-select" required>
<option value="">Seleccione</option>
<?php while ($c = $clientes->fetch_assoc()): ?>
<option value="<?= $c["id_cliente"] ?>" <?= $id_cliente==$c["id_cliente"]?"selected":"" ?>>
<?= $c["nombre"]." ".$c["apellido"] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Mascota</label>
<select name="id_mascota" id="mascota" class="form-select" required>
<option value="">Seleccione cliente</option>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Veterinario</label>
<select name="id_veterinario" class="form-select" required>
<option value="">Seleccione</option>
<?php while ($v = $veterinarios->fetch_assoc()): ?>
<option value="<?= $v["id_veterinario"] ?>" <?= $id_veterinario==$v["id_veterinario"]?"selected":"" ?>>
<?= $v["nombre"]." ".$v["apellido"] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Consultorio</label>
<select name="id_consultorio" id="consultorio" class="form-select" required>
<option value="">Seleccione</option>
<?php
$consultorios->data_seek(0);
while ($co = $consultorios->fetch_assoc()):
?>
<option value="<?= $co["id_consultorio"] ?>" <?= $id_consultorio==$co["id_consultorio"]?"selected":"" ?>>
<?= $co["nombre"] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Motivo de consulta</label>
<select name="motivo" id="servicio" class="form-select" required>
<option value="<?= htmlspecialchars($motivo) ?>">
<?= $motivo ?: "Seleccione consultorio" ?>
</option>
</select>
</div>

<div class="col-md-2">
<label class="form-label">Fecha</label>
<input type="date" name="fecha" class="form-control" value="<?= $fecha ?>" required>
</div>

<div class="col-md-2">
<label class="form-label">Hora</label>
<select name="hora" class="form-select" required>
<option value="">Seleccione</option>
<?php
for ($h=8; $h<=18; $h++) {
    foreach ([0,30] as $m) {
        $time = sprintf("%02d:%02d:00",$h,$m);
        $sel = ($hora==$time)?"selected":"";
        echo "<option value='$time' $sel>$time</option>";
    }
}
?>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Estado</label>
<select name="estado" class="form-select">
<?php foreach ($estados as $e): ?>
<option <?= $estado==$e?"selected":"" ?>><?= $e ?></option>
<?php endforeach; ?>
</select>
</div>

</div>

<div class="mt-4">
<button class="btn btn-success">💾 Guardar</button>
<a href="cita.php" class="btn btn-secondary">Cancelar</a>
</div>

</form>
</div>
</div>

<!-- LISTADO -->
<div class="card shadow-sm">
<div class="card-body">
<h6 class="mb-3">📋 Lista de Citas</h6>

<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Cliente</th>
<th>Mascota</th>
<th>Veterinario</th>
<th>Consultorio</th>
<th>Fecha</th>
<th>Hora</th>
<th>Motivo</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($c = $citas->fetch_assoc()): ?>
<tr>
<td><?= $c["cliente_nombre"]." ".$c["cliente_apellido"] ?></td>
<td><?= $c["mascota_nombre"] ?></td>
<td><?= $c["vet_nombre"]." ".$c["vet_apellido"] ?></td>
<td><?= $c["consultorio_nombre"] ?></td>
<td><?= $c["fecha"] ?></td>
<td><?= $c["hora"] ?></td>
<td><?= $c["motivo"] ?></td>
<td><?= $c["estado"] ?></td>
<td>
<a href="?editar=<?= $c["id_cita"] ?>">✏️</a>
<a href="?eliminar=<?= $c["id_cita"] ?>" onclick="return confirm('¿Eliminar?')">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

</main>

<!-- FOOTER OFICIAL -->
<footer class="footer-vet mt-auto">
    <div class="container text-center">
        <p class="fw-semibold mb-1">🐾 Veterinaria Santiago Barrera</p>
        <p class="text-muted mb-2">Cuidado profesional y amor para tus mascotas</p>
        <div class="d-flex justify-content-center gap-3 mb-2">
            <span>🟢 WhatsApp: 317 680 1793</span>
            <span>📸 Instagram: @santiagobarreraveterinario</span>
        </div>
        <small class="text-muted">© 2025 Veterinaria Santiago Barrera</small>
    </div>
</footer>

<script>
const mascotas = <?= json_encode($mascotas->fetch_all(MYSQLI_ASSOC)) ?>;

const clienteSelect = document.getElementById("cliente");
const mascotaSelect = document.getElementById("mascota");
const consultorioSelect = document.getElementById("consultorio");
const servicioSelect = document.getElementById("servicio");

function cargarMascotas(clienteId, selected = "") {
    mascotaSelect.innerHTML = "<option value=''>Seleccione</option>";
    mascotas.forEach(m => {
        if (m.id_cliente == clienteId) {
            let opt = document.createElement("option");
            opt.value = m.id_mascota;
            opt.textContent = m.nombre;
            if (m.id_mascota == selected) opt.selected = true;
            mascotaSelect.appendChild(opt);
        }
    });
}

clienteSelect.addEventListener("change", () => cargarMascotas(clienteSelect.value));

consultorioSelect.addEventListener("change", () => {
    const id = consultorioSelect.value;
    servicioSelect.innerHTML = "<option>Cargando...</option>";

    if (!id) {
        servicioSelect.innerHTML = "<option>Seleccione consultorio</option>";
        return;
    }

    fetch(`cita.php?ajax=servicios&id_consultorio=${id}`)
        .then(res => res.json())
        .then(data => {
            servicioSelect.innerHTML = "<option value=''>Seleccione</option>";
            data.forEach(s => {
                servicioSelect.innerHTML +=
                    `<option value="${s.nombre}">${s.nombre}</option>`;
            });
        });
});

<?php if ($id_cliente && $id_mascota): ?>
cargarMascotas("<?= $id_cliente ?>","<?= $id_mascota ?>");
<?php endif; ?>
</script>

</body>
</html>
