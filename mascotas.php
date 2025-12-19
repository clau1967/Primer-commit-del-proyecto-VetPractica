<?php include 'includes/header.php'; ?>

<?php
$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión: " . $conexion->connect_error);

// Guardar mascota
if (isset($_POST['guardar'])) {
    $nombre = $_POST['nombre'];
    $especie = $_POST['especie'];
    $raza = $_POST['raza'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $id_cliente = $_POST['id_cliente'];

    $stmt = $conexion->prepare("INSERT INTO mascotas (nombre, especie, raza, fecha_nacimiento, genero, id_cliente) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nombre, $especie, $raza, $fecha_nacimiento, $genero, $id_cliente);
    $stmt->execute();
    $stmt->close();
    header("Location: mascotas.php");
    exit;
}

// Editar mascota
if (isset($_POST['editar'])) {
    $id = $_POST['id_mascota'];
    $nombre = $_POST['nombre'];
    $especie = $_POST['especie'];
    $raza = $_POST['raza'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $id_cliente = $_POST['id_cliente'];

    $stmt = $conexion->prepare("UPDATE mascotas SET nombre=?, especie=?, raza=?, fecha_nacimiento=?, genero=?, id_cliente=? WHERE id_mascota=?");
    $stmt->bind_param("ssssssi", $nombre, $especie, $raza, $fecha_nacimiento, $genero, $id_cliente, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: mascotas.php");
    exit;
}

// Eliminar mascota
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM mascotas WHERE id_mascota=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: mascotas.php");
    exit;
}

// Clientes para select
$clientes = $conexion->query("SELECT id_cliente, nombre, apellido FROM clientes ORDER BY nombre ASC");

// Mascotas con edad calculada
$mascotas = $conexion->query("
    SELECT m.id_mascota, m.nombre, m.especie, m.raza, m.fecha_nacimiento, m.genero,
           c.nombre AS nombre_cliente, c.apellido AS apellido_cliente,
           TIMESTAMPDIFF(YEAR, m.fecha_nacimiento, CURDATE()) AS edad_anios,
           TIMESTAMPDIFF(MONTH, m.fecha_nacimiento, CURDATE()) % 12 AS edad_meses
    FROM mascotas m
    LEFT JOIN clientes c ON m.id_cliente = c.id_cliente
    ORDER BY m.id_mascota DESC
");

// Preparar edición
$editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $res = $conexion->query("SELECT * FROM mascotas WHERE id_mascota = $id");
    $editar = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mascotas - Veterinaria Santiago Barrera</title>
<link rel="stylesheet" href="stylos.css">
<style>
body { font-family: Arial, sans-serif; background:#f4f4f9; margin:0; padding:0; }
.header { display:flex; justify-content:space-between; align-items:center; padding:10px 30px; background:#ffb703; color:white; }
.header img { height:50px; }
.header nav a { margin:0 10px; color:white; text-decoration:none; font-weight:bold; }
.header nav a:hover { text-decoration:underline; }
.contenedor { max-width:950px; margin:20px auto; padding:20px; background:white; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2 { color:#023047; text-align:center; }
input, select { width:100%; padding:10px; margin:6px 0; border-radius:8px; border:1px solid #ccc; font-size:16px; }
input:focus, select:focus { border-color:#ffb703; outline:none; box-shadow:0 0 5px rgba(255,183,3,0.4);}
.btn { padding:10px 18px; border:none; border-radius:8px; background:#ffb703; color:white; font-weight:bold; cursor:pointer; margin-top:10px; transition:0.3s;}
.btn:hover { background:#fb8500; }
.tabla { width:100%; border-collapse: collapse; margin-top:20px; }
.tabla th, .tabla td { border:1px solid #ddd; padding:12px; text-align:center; }
.tabla th { background:#ffb703; color:white; }
.fila:hover { background:#fef3d6; }
.btn-tabla { padding:5px 12px; border-radius:5px; color:white; text-decoration:none; margin:0 5px; display:inline-block;}
.editar { background:#219ebc; }
.eliminar { background:#e63946; }
.genero-contenedor { display:flex; gap:10px; margin:6px 0; }
.genero-contenedor label { flex:1; text-align:center; padding:8px; border-radius:20px; border:1px solid #ccc; cursor:pointer; transition:0.3s;}
.genero-contenedor input { display:none; }
.genero-contenedor label.activo { background:#ffb703; border-color:#ffb703; color:white; }
.select-cliente { width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; }
.volver { text-align:center; margin:20px 0; }
footer { text-align:center; padding:20px; background:#023047; color:white; margin-top:20px; border-radius:0 0 10px 10px; }
</style>
<script>
function toggleGenero(el) {
    let labels = el.parentNode.querySelectorAll('label');
    labels.forEach(l => l.classList.remove('activo'));
    el.classList.add('activo');
}
</script>
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
<h2>Gestión de Mascotas</h2>
<form action="mascotas.php" method="POST">
<input type="hidden" name="id_mascota" value="<?= $editar ? $editar['id_mascota'] : '' ?>">
<input type="text" name="nombre" placeholder="Nombre de la mascota" value="<?= $editar ? $editar['nombre'] : '' ?>" required>
<input type="text" name="especie" placeholder="Especie" value="<?= $editar ? $editar['especie'] : '' ?>" required>
<input type="text" name="raza" placeholder="Raza" value="<?= $editar ? $editar['raza'] : '' ?>" required>
<label>Fecha de nacimiento:</label>
<input type="date" name="fecha_nacimiento" value="<?= $editar ? $editar['fecha_nacimiento'] : '' ?>" required>
<div class="genero-contenedor">
<label class="<?= ($editar && $editar['genero']=='Macho')?'activo':'' ?>" onclick="toggleGenero(this)">
<input type="radio" name="genero" value="Macho" <?= ($editar && $editar['genero']=='Macho')?'checked':'' ?>>Macho
</label>
<label class="<?= ($editar && $editar['genero']=='Hembra')?'activo':'' ?>" onclick="toggleGenero(this)">
<input type="radio" name="genero" value="Hembra" <?= ($editar && $editar['genero']=='Hembra')?'checked':'' ?>>Hembra
</label>
</div>
<select name="id_cliente" class="select-cliente" required>
<option value="">-- Seleccione Cliente --</option>
<?php
$clientes = $conexion->query("SELECT id_cliente, nombre, apellido FROM clientes ORDER BY nombre ASC");
while($c = $clientes->fetch_assoc()) {
    $sel = ($editar && $editar['id_cliente']==$c['id_cliente']) ? 'selected' : '';
    echo "<option value='{$c['id_cliente']}' $sel>{$c['nombre']} {$c['apellido']}</option>";
}
?>
</select>
<button type="submit" name="<?= $editar ? 'editar' : 'guardar' ?>" class="btn"><?= $editar?'Actualizar':'➕ Guardar' ?></button>
</form>
</div>

<div class="contenedor">
<h2>Lista de Mascotas</h2>
<table class="tabla">
<tr><th>ID</th><th>Nombre</th><th>Especie</th><th>Raza</th><th>Edad</th><th>Género</th><th>Propietario</th><th>Acciones</th></tr>
<?php while($m=$mascotas->fetch_assoc()): ?>
<tr class="fila">
<td><?= $m['id_mascota'] ?></td>
<td><?= $m['nombre'] ?></td>
<td><?= $m['especie'] ?></td>
<td><?= $m['raza'] ?></td>
<td><?= $m['edad_anios'].' años '.$m['edad_meses'].' meses' ?></td>
<td><?= $m['genero'] ?></td>
<td><?= $m['nombre_cliente'].' '.$m['apellido_cliente'] ?></td>
<td>
<a href="mascotas.php?editar=<?= $m['id_mascota'] ?>" class="btn-tabla editar">✏ Editar</a>
<a href="mascotas.php?eliminar=<?= $m['id_mascota'] ?>" class="btn-tabla eliminar" onclick="return confirm('¿Eliminar mascota?')">🗑 Eliminar</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<div class="volver">
<a href="index.php" class="btn">🏠 Volver al inicio</a>
</div>

<footer>
© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados.<br>
🟢 WhatsApp 3176801793 | @santiagobarreraveterinario
</footer>

</body>
</html>


<?php include 'includes/footer.php'; ?>