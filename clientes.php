<?php
// clientes.php — idéntico en diseño a mascotas.php, usa la tabla reales (id_cliente, nombre, apellido, correo, telefono, direccion)

$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión: " . $conexion->connect_error);

// -------------------------
// GUARDAR CLIENTE
// -------------------------
if (isset($_POST['guardar'])) {
    $nombre   = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo   = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion= $_POST['direccion'];

    $stmt = $conexion->prepare("INSERT INTO clientes (nombre, apellido, correo, telefono, direccion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $apellido, $correo, $telefono, $direccion);
    $stmt->execute();
    $stmt->close();

    header("Location: clientes.php");
    exit;
}

// -------------------------
// EDITAR CLIENTE (POST)
// -------------------------
if (isset($_POST['editar'])) {
    $id       = intval($_POST['id_cliente']);
    $nombre   = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo   = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion= $_POST['direccion'];

    $stmt = $conexion->prepare("UPDATE clientes SET nombre=?, apellido=?, correo=?, telefono=?, direccion=? WHERE id_cliente=?");
    $stmt->bind_param("sssssi", $nombre, $apellido, $correo, $telefono, $direccion, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: clientes.php");
    exit;
}

// -------------------------
// ELIMINAR CLIENTE (GET)
// -------------------------
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conexion->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: clientes.php");
    exit;
}

// -------------------------
// PREPARAR EDICIÓN (GET)
// -------------------------
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM clientes WHERE id_cliente = $id LIMIT 1");
    if ($res && $res->num_rows) $editar = $res->fetch_assoc();
}

// -------------------------
// LISTAR CLIENTES
// -------------------------
$clientes = $conexion->query("SELECT * FROM clientes ORDER BY id_cliente DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Clientes - Veterinaria Santiago Barrera</title>
<link rel="stylesheet" href="stylos.css">
<style>
/* Inline minor adjustments to match mascotas.php visual exactly */
body { font-family: Arial, sans-serif; background:#f4f4f9; margin:0; padding:0; }
.header { display:flex; justify-content:space-between; align-items:center; padding:10px 30px; background:#ffb703; color:white; }
.header img { height:50px; }
.header nav a { margin:0 10px; color:white; text-decoration:none; font-weight:bold; }
.header nav a:hover { text-decoration:underline; }
.contenedor { max-width:950px; margin:20px auto; padding:20px; background:white; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2 { color:#023047; text-align:center; margin-top:0; }
input { width:100%; padding:10px; margin:6px 0; border-radius:8px; border:1px solid #ccc; font-size:16px; }
input:focus { border-color:#ffb703; outline:none; box-shadow:0 0 5px rgba(255,183,3,0.4);}
.btn { padding:10px 18px; border:none; border-radius:8px; background:#ffb703; color:white; font-weight:bold; cursor:pointer; margin-top:10px; transition:0.3s;}
.btn:hover { background:#fb8500; }
.tabla { width:100%; border-collapse: collapse; margin-top:20px; }
.tabla th, .tabla td { border:1px solid #ddd; padding:12px; text-align:center; }
.tabla th { background:#ffb703; color:white; }
.fila:hover { background:#fef3d6; }
.btn-tabla { padding:6px 10px; border-radius:5px; color:white; text-decoration:none; margin:0 5px; display:inline-block;}
.editar { background:#219ebc; }
.eliminar { background:#e63946; }
.form-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:12px; }
.form-grid .full { grid-column: 1 / -1; }
@media (max-width:700px){ .form-grid { grid-template-columns: 1fr; } }
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
        <a href="citas.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<div class="contenedor">
    <h2>Gestión de Clientes</h2>

    <form action="clientes.php" method="POST" class="form-grid">
        <input type="hidden" name="id_cliente" value="<?= $editar ? htmlspecialchars($editar['id_cliente']) : '' ?>">

        <input type="text" name="nombre" placeholder="Nombre" required value="<?= $editar ? htmlspecialchars($editar['nombre']) : '' ?>">
        <input type="text" name="apellido" placeholder="Apellido" required value="<?= $editar ? htmlspecialchars($editar['apellido']) : '' ?>">

        <input type="email" name="correo" placeholder="Correo" required value="<?= $editar ? htmlspecialchars($editar['correo']) : '' ?>">
        <input type="text" name="telefono" placeholder="Teléfono" value="<?= $editar ? htmlspecialchars($editar['telefono']) : '' ?>">

        <input type="text" name="direccion" placeholder="Dirección" class="full" value="<?= $editar ? htmlspecialchars($editar['direccion']) : '' ?>">

        <button type="submit" name="<?= $editar ? 'editar' : 'guardar' ?>" class="btn full"><?= $editar ? 'Actualizar' : '➕ Guardar' ?></button>
    </form>
</div>

<div class="contenedor">
    <h2>Lista de Clientes</h2>

    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while($c = $clientes->fetch_assoc()): ?>
            <tr class="fila">
                <td><?= htmlspecialchars($c['id_cliente']) ?></td>
                <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></td>
                <td><?= htmlspecialchars($c['correo']) ?></td>
                <td><?= htmlspecialchars($c['telefono']) ?></td>
                <td><?= htmlspecialchars($c['direccion']) ?></td>
                <td>
                    <a href="clientes.php?editar=<?= $c['id_cliente'] ?>" class="btn-tabla editar">✏ Editar</a>
                    <a href="clientes.php?eliminar=<?= $c['id_cliente'] ?>" class="btn-tabla eliminar" onclick="return confirm('¿Eliminar cliente?')">🗑 Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="volver" style="text-align:center; margin:20px 0;">
    <a href="index.php" class="btn">🏠 Volver al inicio</a>
</div>

<footer style="text-align:center; padding:20px; background:#023047; color:white; margin-top:20px; border-radius:0 0 10px 10px;">
© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados.<br>
🟢 WhatsApp 3176801793 | @santiagobarreraveterinario
</footer>

</body>
</html>
