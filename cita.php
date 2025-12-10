<?php
// cita.php - Gestión completa de citas (CRUD) - Usa conexión directa como en clientes/mascotas

// ---------- Conexión (directa, credenciales tuyas) ----------
$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión: " . $conexion->connect_error);

// ---------- Manejo CRUD ----------
// GUARDAR
if (isset($_POST['guardar'])) {
    $id_cliente     = intval($_POST['id_cliente']);
    $id_mascota     = intval($_POST['id_mascota']);
    $id_veterinario = intval($_POST['id_veterinario']);
    $id_consultorio = intval($_POST['id_consultorio']);
    $fecha          = $_POST['fecha']; // YYYY-MM-DD
    $hora_12        = $_POST['hora'];  // e.g. "08:15 AM"
    $motivo         = $_POST['motivo'];
    $estado         = $_POST['estado'];

    // Convertir hora 12h a formato 24h y crear DATETIME
    $time24 = date('H:i', strtotime($hora_12));
    $fecha_hora = $fecha . ' ' . $time24 . ':00';

    $stmt = $conexion->prepare("INSERT INTO citas (id_cliente,id_mascota,id_veterinario,id_consultorio,fecha_hora,motivo,estado) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("iiiisss", $id_cliente, $id_mascota, $id_veterinario, $id_consultorio, $fecha_hora, $motivo, $estado);
    $stmt->execute();
    $stmt->close();

    header("Location: cita.php");
    exit;
}

// EDITAR
if (isset($_POST['editar'])) {
    $id_cita        = intval($_POST['id_cita']);
    $id_cliente     = intval($_POST['id_cliente']);
    $id_mascota     = intval($_POST['id_mascota']);
    $id_veterinario = intval($_POST['id_veterinario']);
    $id_consultorio = intval($_POST['id_consultorio']);
    $fecha          = $_POST['fecha']; // YYYY-MM-DD
    $hora_12        = $_POST['hora'];  // e.g. "08:15 AM"
    $motivo         = $_POST['motivo'];
    $estado         = $_POST['estado'];

    $time24 = date('H:i', strtotime($hora_12));
    $fecha_hora = $fecha . ' ' . $time24 . ':00';

    $stmt = $conexion->prepare("UPDATE citas SET id_cliente=?, id_mascota=?, id_veterinario=?, id_consultorio=?, fecha_hora=?, motivo=?, estado=? WHERE id_cita=?");
    $stmt->bind_param("iiiisssi", $id_cliente, $id_mascota, $id_veterinario, $id_consultorio, $fecha_hora, $motivo, $estado, $id_cita);
    $stmt->execute();
    $stmt->close();

    header("Location: cita.php");
    exit;
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conexion->prepare("DELETE FROM citas WHERE id_cita = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: cita.php");
    exit;
}

// PREPARAR EDICIÓN (GET)
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM citas WHERE id_cita = $id LIMIT 1");
    if ($res && $res->num_rows) $editar = $res->fetch_assoc();
}

// ---------- Listados para selects ----------
// Clientes y demás
$clientes     = $conexion->query("SELECT id_cliente, nombre, apellido FROM clientes ORDER BY nombre, apellido");
$veterinarios = $conexion->query("SELECT id_veterinario, nombre, apellido FROM veterinarios ORDER BY nombre, apellido");
$consultorios = $conexion->query("SELECT id_consultorio, nombre FROM consultorios ORDER BY nombre");

// Lista de citas (para la tabla)
$citas = $conexion->query("
    SELECT c.*, cl.nombre AS cliente_nombre, cl.apellido AS cliente_apellido,
           m.nombre AS mascota_nombre,
           v.nombre AS vet_nombre, v.apellido AS vet_apellido,
           co.nombre AS consultorio_nombre
    FROM citas c
    JOIN clientes cl ON cl.id_cliente = c.id_cliente
    JOIN mascotas m ON m.id_mascota = c.id_mascota
    JOIN veterinarios v ON v.id_veterinario = c.id_veterinario
    JOIN consultorios co ON co.id_consultorio = c.id_consultorio
    ORDER BY c.fecha_hora DESC
");

// ---------- Manejo selección de cliente para cargar mascotas (usando GET para no interferir con POST) ----------
$selected_cliente = null;
if (isset($_GET['cliente']) && $_GET['cliente'] !== '') {
    $selected_cliente = intval($_GET['cliente']);
} elseif ($editar) {
    $selected_cliente = $editar['id_cliente'];
}

// Cargar mascotas del cliente seleccionado (si hay)
$mascotas = [];
if ($selected_cliente) {
    $res_m = $conexion->query("SELECT id_mascota, nombre FROM mascotas WHERE id_cliente = $selected_cliente ORDER BY nombre");
    while ($m = $res_m->fetch_assoc()) $mascotas[] = $m;
}

// ---------- Generar horas cada 15 minutos en formato 12h AM/PM entre 08:00 y 20:00 ----------
function generar_horas_12h($inicio="08:00", $fin="20:00") {
    $horas = [];
    $time = strtotime($inicio);
    $end  = strtotime($fin);
    while ($time <= $end) {
        $horas[] = date("h:i A", $time); // formato 12h
        $time = strtotime("+15 minutes", $time);
    }
    return $horas;
}
$horas = generar_horas_12h("08:00", "20:00");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cita - Veterinaria Santiago Barrera</title>
<link rel="stylesheet" href="stylos.css">
<style>
/* Mantengo tu estilo principal para que no cambie diseño */
body { font-family: Arial, sans-serif; background:#f4f4f9; margin:0; padding:0; }
.header { display:flex; justify-content:space-between; align-items:center; padding:10px 30px; background:#ffb703; color:white; }
.header img { height:50px; }
.header nav a { margin:0 10px; color:white; text-decoration:none; font-weight:bold; }
.header nav a:hover { text-decoration:underline; }
.contenedor { max-width:950px; margin:20px auto; padding:20px; background:white; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2 { color:#023047; text-align:center; margin-top:0; }
select, input, textarea { width:100%; padding:10px; margin:6px 0; border-radius:8px; border:1px solid #ccc; font-size:16px; }
select:focus, input:focus, textarea:focus { border-color:#ffb703; outline:none; box-shadow:0 0 5px rgba(255,183,3,0.4);}
.btn { padding:10px 18px; border:none; border-radius:8px; background:#ffb703; color:white; font-weight:bold; cursor:pointer; margin-top:10px; transition:0.3s;}
.btn:hover { background:#fb8500; }
.tabla { width:100%; border-collapse: collapse; margin-top:20px; }
.tabla th, .tabla td { border:1px solid #ddd; padding:12px; text-align:center; vertical-align:middle; }
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
        <a href="cita.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<div class="contenedor">
    <h2><?= $editar ? 'Editar Cita' : 'Nueva Cita' ?></h2>

    <!-- Form separado para seleccionar cliente (GET) para cargar mascotas sin interferir con POST -->
    <form action="cita.php" method="GET" style="margin-bottom:8px;">
        <select name="cliente" onchange="this.form.submit()">
            <option value="">-- Seleccione Cliente --</option>
            <?php
            // Rewind pointer for $clientes to reuse below - fetch clients into array first
            $clientes_array = [];
            $clientes = $conexion->query("SELECT id_cliente, nombre, apellido FROM clientes ORDER BY nombre, apellido");
            while ($cl = $clientes->fetch_assoc()) {
                $clientes_array[] = $cl;
                $sel = ($selected_cliente && $selected_cliente == $cl['id_cliente']) ? 'selected' : '';
                echo "<option value='{$cl['id_cliente']}' $sel>" . htmlspecialchars($cl['nombre'].' '.$cl['apellido']) . "</option>";
            }
            ?>
        </select>
    </form>

    <!-- Form principal para crear/editar (POST) -->
    <form action="cita.php<?= $selected_cliente ? '?cliente='.$selected_cliente : '' ?>" method="POST" class="form-grid">
        <input type="hidden" name="id_cita" value="<?= $editar ? htmlspecialchars($editar['id_cita']) : '' ?>">

        <select name="id_cliente" required>
            <option value="">-- Seleccione Cliente --</option>
            <?php foreach ($clientes_array as $cl): 
                $sel = '';
                if ($editar && $editar['id_cliente']==$cl['id_cliente']) $sel = 'selected';
                if (!$editar && $selected_cliente && $selected_cliente == $cl['id_cliente']) $sel = 'selected';
            ?>
                <option value="<?= $cl['id_cliente'] ?>" <?= $sel ?>><?= htmlspecialchars($cl['nombre'].' '.$cl['apellido']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="id_mascota" required>
            <option value="">-- Seleccione Mascota --</option>
            <?php foreach ($mascotas as $m): 
                $sel = ($editar && $editar['id_mascota']==$m['id_mascota']) ? 'selected' : '';
            ?>
                <option value="<?= $m['id_mascota'] ?>" <?= $sel ?>><?= htmlspecialchars($m['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="id_veterinario" required>
            <option value="">-- Seleccione Veterinario --</option>
            <?php
            // rewind veterinarios query and output
            $veterinarios = $conexion->query("SELECT id_veterinario, nombre, apellido FROM veterinarios ORDER BY nombre, apellido");
            while ($v = $veterinarios->fetch_assoc()):
                $sel = ($editar && $editar['id_veterinario']==$v['id_veterinario']) ? 'selected' : '';
            ?>
                <option value="<?= $v['id_veterinario'] ?>" <?= $sel ?>><?= htmlspecialchars($v['nombre'].' '.$v['apellido']) ?></option>
            <?php endwhile; ?>
        </select>

        <select name="id_consultorio" required>
            <option value="">-- Seleccione Consultorio --</option>
            <?php
            $consultorios = $conexion->query("SELECT id_consultorio, nombre FROM consultorios ORDER BY nombre");
            while ($co = $consultorios->fetch_assoc()):
                $sel = ($editar && $editar['id_consultorio']==$co['id_consultorio']) ? 'selected' : '';
            ?>
                <option value="<?= $co['id_consultorio'] ?>" <?= $sel ?>><?= htmlspecialchars($co['nombre']) ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Fecha -->
        <input type="date" name="fecha" required value="<?= $editar ? htmlspecialchars(substr($editar['fecha_hora'],0,10)) : '' ?>">

        <!-- Hora (12h AM/PM) -->
        <select name="hora" required>
            <option value="">-- Seleccione Hora --</option>
            <?php foreach ($horas as $h):
                $selected_h = '';
                if ($editar) {
                    // comparar hora del registro convertida a 12h
                    $hora_registro = date("h:i A", strtotime($editar['fecha_hora']));
                    if ($hora_registro == $h) $selected_h = 'selected';
                }
            ?>
                <option value="<?= $h ?>" <?= $selected_h ?>><?= $h ?></option>
            <?php endforeach; ?>
        </select>

        <textarea name="motivo" class="full" placeholder="Motivo de la cita" required><?= $editar ? htmlspecialchars($editar['motivo']) : '' ?></textarea>

        <select name="estado" required>
            <?php $estados = ['Pendiente','Confirmada','Cancelada','Finalizada']; ?>
            <?php foreach ($estados as $e):
                $sel = ($editar && $editar['estado']==$e) ? 'selected' : '';
            ?>
                <option value="<?= $e ?>" <?= $sel ?>><?= $e ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="<?= $editar ? 'editar' : 'guardar' ?>" class="btn full"><?= $editar ? 'Actualizar' : '➕ Guardar' ?></button>
    </form>
</div>

<div class="contenedor">
    <h2>Lista de Citas</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
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
        <?php while($c = $citas->fetch_assoc()): 
            $fecha = date("d/m/Y", strtotime($c['fecha_hora']));
            $hora  = date("h:i A", strtotime($c['fecha_hora']));
        ?>
            <tr class="fila">
                <td><?= htmlspecialchars($c['id_cita']) ?></td>
                <td><?= htmlspecialchars($c['cliente_nombre'].' '.$c['cliente_apellido']) ?></td>
                <td><?= htmlspecialchars($c['mascota_nombre']) ?></td>
                <td><?= htmlspecialchars($c['vet_nombre'].' '.$c['vet_apellido']) ?></td>
                <td><?= htmlspecialchars($c['consultorio_nombre']) ?></td>
                <td><?= $fecha ?></td>
                <td><?= $hora ?></td>
                <td><?= htmlspecialchars($c['motivo']) ?></td>
                <td><?= htmlspecialchars($c['estado']) ?></td>
                <td>
                    <a href="cita.php?editar=<?= $c['id_cita'] ?>" class="btn-tabla editar">✏ Editar</a>
                    <a href="cita.php?eliminar=<?= $c['id_cita'] ?>" class="btn-tabla eliminar" onclick="return confirm('¿Eliminar cita?')">🗑 Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div style="text-align:center; margin:20px 0;">
    <a href="index.php" class="btn">🏠 Volver al inicio</a>
</div>

<footer style="text-align:center; padding:20px; background:#023047; color:white; margin-top:20px; border-radius:0 0 10px 10px;">
© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados.<br>
🟢 WhatsApp 3176801793 | @santiagobarreraveterinario
</footer>

</body>
</html>
