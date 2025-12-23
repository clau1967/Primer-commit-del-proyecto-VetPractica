<?php
// ===============================
// CONEXIÓN BD (MISMA QUE CLIENTES)
// ===============================
$conn = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// ===============================
// FUNCIÓN CALCULAR EDAD (AÑOS Y MESES)
// ===============================
function calcularEdad($fecha) {
    if (!$fecha) return "—";

    $nacimiento = new DateTime($fecha);
    $hoy = new DateTime();
    $diff = $hoy->diff($nacimiento);

    if ($diff->y > 0) {
        return $diff->y . " años " . $diff->m . " meses";
    } else {
        return $diff->m . " meses";
    }
}

// ===============================
// VARIABLES
// ===============================
$id_mascota = "";
$id_cliente = "";
$nombre = "";
$especie = "";
$raza = "";
$fecha_nacimiento = "";
$genero = "";

// ===============================
// GUARDAR / ACTUALIZAR
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_mascota       = $_POST["id_mascota"] ?? "";
    $id_cliente       = $_POST["id_cliente"];
    $nombre           = $_POST["nombre"];
    $especie          = $_POST["especie"];
    $raza             = $_POST["raza"];
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $genero           = $_POST["genero"];

    if ($id_mascota == "") {

        $stmt = $conn->prepare(
            "INSERT INTO mascotas 
            (id_cliente, nombre, especie, raza, fecha_nacimiento, genero)
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssss",
            $id_cliente,
            $nombre,
            $especie,
            $raza,
            $fecha_nacimiento,
            $genero
        );
        $stmt->execute();
        header("Location: mascotas.php");
        exit;

    } else {

        $stmt = $conn->prepare(
            "UPDATE mascotas SET
                id_cliente=?,
                nombre=?,
                especie=?,
                raza=?,
                fecha_nacimiento=?,
                genero=?
             WHERE id_mascota=?"
        );
        $stmt->bind_param("isssssi",
            $id_cliente,
            $nombre,
            $especie,
            $raza,
            $fecha_nacimiento,
            $genero,
            $id_mascota
        );
        $stmt->execute();
        header("Location: mascotas.php");
        exit;
    }
}

// ===============================
// EDITAR
// ===============================
if (isset($_GET["editar"])) {
    $id_mascota = $_GET["editar"];

    $stmt = $conn->prepare("SELECT * FROM mascotas WHERE id_mascota=?");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_cliente       = $row["id_cliente"];
        $nombre           = $row["nombre"];
        $especie          = $row["especie"];
        $raza             = $row["raza"];
        $fecha_nacimiento = $row["fecha_nacimiento"];
        $genero           = $row["genero"];
    }
}

// ===============================
// ELIMINAR
// ===============================
if (isset($_GET["eliminar"])) {
    $stmt = $conn->prepare("DELETE FROM mascotas WHERE id_mascota=?");
    $stmt->bind_param("i", $_GET["eliminar"]);
    $stmt->execute();
    header("Location: mascotas.php");
    exit;
}

// ===============================
// LISTADOS
// ===============================
$clientes = $conn->query(
    "SELECT id_cliente, CONCAT(nombre,' ',apellido) AS nombre_completo 
     FROM clientes ORDER BY nombre"
);

$mascotas = $conn->query(
    "SELECT m.*, CONCAT(c.nombre,' ',c.apellido) AS cliente
     FROM mascotas m
     INNER JOIN clientes c ON m.id_cliente = c.id_cliente
     ORDER BY m.id_mascota DESC"
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Mascotas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="stylos.css">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- HEADER -->
<header class="header">
    <div class="header-left">
        <img src="img/logo.png">
        <span class="header-title">Veterinaria Santiago Barrera</span>
    </div>
    <nav class="header-nav">
        <a href="index.php">Inicio</a>
        <a href="clientes.php">Clientes</a>
        <a href="mascotas.php" class="active">Mascotas</a>
        <a href="citas.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<main class="flex-fill container my-4">

<div class="title-vet">
    <h4>Gestión de Mascotas</h4>
</div>

<!-- FORMULARIO (NO TOCADO) -->
<div class="card mb-4 shadow-sm">
<div class="card-body">
<form method="POST">
<input type="hidden" name="id_mascota" value="<?= $id_mascota ?>">

<div class="row g-3">
<div class="col-md-4">
<label>Cliente</label>
<select name="id_cliente" class="form-select" required>
<option value="">Seleccione cliente</option>
<?php while ($cl = $clientes->fetch_assoc()): ?>
<option value="<?= $cl["id_cliente"] ?>" <?= $id_cliente==$cl["id_cliente"]?"selected":"" ?>>
<?= $cl["nombre_completo"] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-4">
<label>Nombre</label>
<input type="text" name="nombre" class="form-control" required value="<?= $nombre ?>">
</div>

<div class="col-md-4">
<label>Especie</label>
<input type="text" name="especie" class="form-control" value="<?= $especie ?>">
</div>

<div class="col-md-4">
<label>Raza</label>
<input type="text" name="raza" class="form-control" value="<?= $raza ?>">
</div>

<div class="col-md-4">
<label>Fecha de nacimiento</label>
<input type="date" name="fecha_nacimiento" class="form-control" value="<?= $fecha_nacimiento ?>">
</div>

<div class="col-md-4">
<label>Género</label>
<select name="genero" class="form-select">
<option value="">Seleccione</option>
<option value="Macho" <?= $genero=="Macho"?"selected":"" ?>>Macho</option>
<option value="Hembra" <?= $genero=="Hembra"?"selected":"" ?>>Hembra</option>
</select>
</div>
</div>

<div class="mt-4">
<button class="btn btn-success">💾 Guardar</button>
<a href="mascotas.php" class="btn btn-secondary">Cancelar</a>
</div>
</form>
</div>
</div>

<!-- LISTADO CON EDAD -->
<div class="card shadow-sm">
<div class="card-body">
<table class="table table-hover">
<thead class="table-light">
<tr>
<th>ID</th>
<th>Mascota</th>
<th>Cliente</th>
<th>Especie</th>
<th>Raza</th>
<th>Edad</th>
<th class="text-center">Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($m = $mascotas->fetch_assoc()): ?>
<tr>
<td><?= $m["id_mascota"] ?></td>
<td><?= $m["nombre"] ?></td>
<td><?= $m["cliente"] ?></td>
<td><?= $m["especie"] ?></td>
<td><?= $m["raza"] ?></td>
<td><?= calcularEdad($m["fecha_nacimiento"]) ?></td>
<td class="text-center">
<a href="mascotas.php?editar=<?= $m["id_mascota"] ?>" class="btn-action btn-edit">✏️</a>
<a href="mascotas.php?eliminar=<?= $m["id_mascota"] ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar mascota?')">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

</main>

<!-- FOOTER -->
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

</body>
</html>
