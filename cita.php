<?php include 'includes/header.php'; ?>

<?php
// ================== CONEXIÓN ==================
$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión");

// ================== GUARDAR ==================
if (isset($_POST['guardar'])) {
    $stmt = $conexion->prepare(
        "INSERT INTO citas (id_cliente,id_mascota,id_veterinario,id_consultorio,fecha,hora,motivo,estado)
         VALUES (?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        "iiiissss",
        $_POST['id_cliente'],
        $_POST['id_mascota'],
        $_POST['id_veterinario'],
        $_POST['id_consultorio'],
        $_POST['fecha'],
        $_POST['hora'],
        $_POST['motivo'],
        $_POST['estado']
    );
    $stmt->execute();
    header("Location: cita.php");
    exit;
}

// ================== EDITAR ==================
if (isset($_POST['editar'])) {
    $stmt = $conexion->prepare(
        "UPDATE citas SET id_cliente=?, id_mascota=?, id_veterinario=?, id_consultorio=?, fecha=?, hora=?, motivo=?, estado=?
         WHERE id_cita=?"
    );
    $stmt->bind_param(
        "iiiissssi",
        $_POST['id_cliente'],
        $_POST['id_mascota'],
        $_POST['id_veterinario'],
        $_POST['id_consultorio'],
        $_POST['fecha'],
        $_POST['hora'],
        $_POST['motivo'],
        $_POST['estado'],
        $_POST['id_cita']
    );
    $stmt->execute();
    header("Location: cita.php");
    exit;
}

// ================== ELIMINAR ==================
if (isset($_GET['eliminar'])) {
    $stmt = $conexion->prepare("DELETE FROM citas WHERE id_cita=?");
    $stmt->bind_param("i", $_GET['eliminar']);
    $stmt->execute();
    header("Location: cita.php");
    exit;
}

// ================== PREPARAR EDICIÓN ==================
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM citas WHERE id_cita=$id LIMIT 1");
    if ($res && $res->num_rows) $editar = $res->fetch_assoc();
}

// ================== DATOS ==================
$clientes = $conexion->query("SELECT id_cliente,nombre,apellido FROM clientes ORDER BY nombre");
$veterinarios = $conexion->query("SELECT id_veterinario,nombre,apellido FROM veterinarios");
$consultorios = $conexion->query("SELECT id_consultorio,nombre FROM consultorios");

$cliente_sel = $_GET['cliente'] ?? ($editar['id_cliente'] ?? '');
$mascotas = [];
if ($cliente_sel) {
    $res = $conexion->query("SELECT id_mascota,nombre FROM mascotas WHERE id_cliente=$cliente_sel");
    while ($m = $res->fetch_assoc()) $mascotas[] = $m;
}

$citas = $conexion->query("
    SELECT c.*, cl.nombre cliente, cl.apellido, m.nombre mascota
    FROM citas c
    JOIN clientes cl ON cl.id_cliente=c.id_cliente
    JOIN mascotas m ON m.id_mascota=c.id_mascota
    ORDER BY c.fecha DESC, c.hora DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Citas - Veterinaria Santiago Barrera</title>
<link rel="stylesheet" href="stylos.css">

<style>
/* ================= DISEÑO UNIFICADO ================= */

body{
    background:#f4f6fb;
    font-family: Arial, sans-serif;
    margin:0;
}

/* HEADER */
.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:10px 30px;
    background:#ffb703;
}
.header img{
    max-height:60px;
}
.header nav a{
    color:white;
    text-decoration:none;
    margin:0 10px;
    font-weight:bold;
}

/* CONTENEDOR */
.contenedor{
    max-width:1000px;
    margin:25px auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,.1);
}

h2,h3{
    color:#023047;
    text-align:center;
}

/* FORMULARIO */
.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
}
.form-grid .full{
    grid-column:1/-1;
}

select,input,textarea{
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
    width:100%;
}

textarea{
    resize:none;
}

/* BOTONES */
.btn{
    padding:12px 22px;
    border:none;
    border-radius:10px;
    background:#ffb703;
    color:white;
    font-weight:bold;
    cursor:pointer;
}
.btn:hover{ background:#fb8500; }

.btn-tabla{
    padding:6px 12px;
    border-radius:6px;
    color:white;
    text-decoration:none;
    font-weight:bold;
}
.editar{ background:#219ebc; }
.eliminar{ background:#e63946; }

/* TABLA */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th,td{
    padding:10px;
    border-bottom:1px solid #ddd;
    text-align:center;
}
th{
    background:#ffb703;
    color:white;
}
tr:hover{ background:#fef3d6; }

/* FOOTER */
footer{
    background:#023047;
    color:white;
    text-align:center;
    padding:15px;
    margin-top:30px;
}
</style>
</head>

<body>

<header class="header">
    <img src="img/logo.png">
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
<h2>Gestión de Citas</h2>

<form method="GET">
    <select name="cliente" onchange="this.form.submit()">
        <option value="">Seleccione Cliente</option>
        <?php while($c=$clientes->fetch_assoc()): ?>
        <option value="<?= $c['id_cliente'] ?>" <?= $cliente_sel==$c['id_cliente']?'selected':'' ?>>
            <?= $c['nombre'].' '.$c['apellido'] ?>
        </option>
        <?php endwhile; ?>
    </select>
</form>

<form method="POST">
<input type="hidden" name="id_cita" value="<?= $editar['id_cita'] ?? '' ?>">
<input type="hidden" name="id_cliente" value="<?= $cliente_sel ?>">

<div class="form-grid">
<select name="id_mascota" required>
<option value="">Mascota</option>
<?php foreach($mascotas as $m): ?>
<option value="<?= $m['id_mascota'] ?>" <?= ($editar && $editar['id_mascota']==$m['id_mascota'])?'selected':'' ?>>
<?= $m['nombre'] ?>
</option>
<?php endforeach; ?>
</select>

<select name="id_veterinario" required>
<option value="">Veterinario</option>
<?php while($v=$veterinarios->fetch_assoc()): ?>
<option value="<?= $v['id_veterinario'] ?>" <?= ($editar && $editar['id_veterinario']==$v['id_veterinario'])?'selected':'' ?>>
<?= $v['nombre'].' '.$v['apellido'] ?>
</option>
<?php endwhile; ?>
</select>

<select name="id_consultorio">
<?php while($co=$consultorios->fetch_assoc()): ?>
<option value="<?= $co['id_consultorio'] ?>" <?= ($editar && $editar['id_consultorio']==$co['id_consultorio'])?'selected':'' ?>>
<?= $co['nombre'] ?>
</option>
<?php endwhile; ?>
</select>

<input type="date" name="fecha" required value="<?= $editar['fecha'] ?? '' ?>">
<input type="time" name="hora" required value="<?= $editar['hora'] ?? '' ?>">

<select name="motivo" class="full">
<option>Consulta general</option>
<option>Vacunación</option>
<option>Control</option>
<option>Cirugía</option>
<option>Urgencia</option>
</select>

<select name="estado" class="full">
<option <?= ($editar && $editar['estado']=='Pendiente')?'selected':'' ?>>Pendiente</option>
<option <?= ($editar && $editar['estado']=='Confirmada')?'selected':'' ?>>Confirmada</option>
<option <?= ($editar && $editar['estado']=='Cancelada')?'selected':'' ?>>Cancelada</option>
<option <?= ($editar && $editar['estado']=='Finalizada')?'selected':'' ?>>Finalizada</option>
</select>

<button name="<?= $editar?'editar':'guardar' ?>" class="btn full">
<?= $editar?'Actualizar':'➕ Guardar' ?>
</button>
</div>
</form>
</div>

<div class="contenedor">
<h3>Lista de Citas</h3>
<table>
<tr>
<th>Cliente</th><th>Mascota</th><th>Fecha</th><th>Hora</th><th>Acciones</th>
</tr>
<?php while($c=$citas->fetch_assoc()): ?>
<tr>
<td><?= $c['cliente'].' '.$c['apellido'] ?></td>
<td><?= $c['mascota'] ?></td>
<td><?= $c['fecha'] ?></td>
<td><?= substr($c['hora'],0,5) ?></td>
<td>
<a href="cita.php?editar=<?= $c['id_cita'] ?>" class="btn-tabla editar">Editar</a>
<a href="cita.php?eliminar=<?= $c['id_cita'] ?>" class="btn-tabla eliminar">Eliminar</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<footer>
© 2025 Veterinaria Santiago Barrera
</footer>

</body>
</html>


<?php include 'includes/footer.php'; ?>