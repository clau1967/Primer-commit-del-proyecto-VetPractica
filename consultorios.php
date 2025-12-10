<?php
// consultorios.php - CRUD completo

$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión: " . $conexion->connect_error);

// GUARDAR
if (isset($_POST['guardar'])) {
    $nombre = $_POST['nombre'];

    $stmt = $conexion->prepare("INSERT INTO consultorios (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->close();

    header("Location: consultorios.php");
    exit;
}

// EDITAR
if (isset($_POST['editar'])) {
    $id     = intval($_POST['id_consultorio']);
    $nombre = $_POST['nombre'];

    $stmt = $conexion->prepare("UPDATE consultorios SET nombre=? WHERE id_consultorio=?");
    $stmt->bind_param("si", $nombre, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: consultorios.php");
    exit;
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);

    $stmt = $conexion->prepare("DELETE FROM consultorios WHERE id_consultorio=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: consultorios.php");
    exit;
}

// CONSULTA PARA FORMULARIO DE EDICIÓN
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM consultorios WHERE id_consultorio=$id LIMIT 1");
    if ($res && $res->num_rows) $editar = $res->fetch_assoc();
}

// LISTA TABLA
$lista = $conexion->query("SELECT * FROM consultorios ORDER BY id_consultorio");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Consultorios - Veterinaria Santiago Barrera</title>
<link rel="stylesheet" href="stylos.css">
<style>
body { font-family: Arial, sans-serif; background:#f4f4f9; margin:0; padding:0; }
.header { display:flex; justify-content:space-between; align-items:center; padding:10px 30px; background:#ffb703; color:white; }
.header img { height:50px; }
.header nav a { margin:0 10px; color:white; text-decoration:none; font-weight:bold; }
.header nav a:hover { text-decoration:underline; }
.contenedor { max-width:900px; margin:20px auto; padding:20px; background:white;
    border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2 { color:#023047; text-align:center; }
input { width:100%; padding:10px; margin:6px 0; border-radius:8px; border:1px solid #ccc; font-size:16px; }
input:focus { border-color:#ffb703; outline:none; box-shadow:0 0 5px rgba(255,183,3,0.4);}
.btn { padding:10px 18px; border:none; border-radius:8px; background:#ffb703; color:white; font-weight:bold;
    cursor:pointer; margin-top:10px; transition:0.3s;}
.btn:hover { background:#fb8500; }
.tabla { width:100%; border-collapse: collapse; margin-top:20px; }
.tabla th, .tabla td { border:1px solid #ddd; padding:12px; text-align:center; }
.tabla th { background:#ffb703; color:white; }
.fila:hover { background:#fef3d6; }
.btn-tabla { padding:6px 10px; border-radius:5px; color:white; text-decoration:none; margin:0 5px; }
.editar { background:#219ebc; }
.eliminar { background:#e63946; }
</style>
</head>

<body>

<header class="header">
    <img src="img/logo.png" alt="Logo Veterinaria">
    <h1>Veterinaria Santiago Barrera</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="clientes.php">Clientes</a>
        <a href="mascotas.php">Mascotas</a>
        <a href="cita.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<div class="contenedor">
    <h2><?= $editar ? "Editar Consultorio" : "Nuevo Consultorio" ?></h2>

    <form method="POST">
        <input type="hidden" name="id_consultorio" value="<?= $editar ? $editar['id_consultorio'] : '' ?>">

        <input type="text" name="nombre" placeholder="Nombre del consultorio" required
               value="<?= $editar ? htmlspecialchars($editar['nombre']) : '' ?>">

        <button type="submit" name="<?= $editar ? 'editar' : 'guardar' ?>" class="btn">
            <?= $editar ? "Actualizar" : "➕ Guardar" ?>
        </button>
    </form>
</div>

<div class="contenedor">
    <h2>Lista de Consultorios</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Consultorio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while($c = $lista->fetch_assoc()): ?>
            <tr class="fila">
                <td><?= $c['id_consultorio'] ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td>
                    <a href="consultorios.php?editar=<?= $c['id_consultorio'] ?>" class="btn-tabla editar">✏ Editar</a>
                    <a href="consultorios.php?eliminar=<?= $c['id_consultorio'] ?>" class="btn-tabla eliminar"
                       onclick="return confirm('¿Eliminar consultorio?')">🗑 Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div style="text-align:center; margin:20px 0;">
    <a href="index.php" class="btn">🏠 Volver al inicio</a>
</div>

<footer style="text-align:center; padding:20px; background:#023047; color:white; margin-top:20px;">
© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados.
</footer>

</body>
</html>
