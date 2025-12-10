<?php
// historias.php - Gestión completa de Historias Clínicas

$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión: " . $conexion->connect_error);

// -------------------------
// GUARDAR HISTORIA
// -------------------------
if (isset($_POST['guardar'])) {
    $mascota_id = intval($_POST['mascota_id']);
    $veterinario_id = intval($_POST['veterinario_id']);
    $fecha = $_POST['fecha'] ?: null;
    $motivo = $_POST['motivo'] ?: null;
    $diagnostico = $_POST['diagnostico'] ?: null;
    $tratamiento = $_POST['tratamiento'] ?: null;
    $peso = $_POST['peso'] ?: null;
    $temperatura = $_POST['temperatura'] ?: null;
    $fc = $_POST['frecuencia_cardiaca'] ?: null;
    $fr = $_POST['frecuencia_respiratoria'] ?: null;
    $estado_animo = $_POST['estado_animo'] ?: null;
    $observaciones = $_POST['observaciones'] ?: null;
    $proximo_control = $_POST['proximo_control'] ?: null;

    $stmt = $conexion->prepare("INSERT INTO historias_clinicas 
        (mascota_id, veterinario_id, fecha, motivo, diagnostico, tratamiento, peso, temperatura, frecuencia_cardiaca, frecuencia_respiratoria, estado_animo, observaciones, proximo_control) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssddiiiss", $mascota_id, $veterinario_id, $fecha, $motivo, $diagnostico, $tratamiento, $peso, $temperatura, $fc, $fr, $estado_animo, $observaciones, $proximo_control);
    $stmt->execute();
    $stmt->close();

    header("Location: historias.php");
    exit;
}

// -------------------------
// EDITAR HISTORIA
// -------------------------
if (isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $mascota_id = intval($_POST['mascota_id']);
    $veterinario_id = intval($_POST['veterinario_id']);
    $fecha = $_POST['fecha'] ?: null;
    $motivo = $_POST['motivo'] ?: null;
    $diagnostico = $_POST['diagnostico'] ?: null;
    $tratamiento = $_POST['tratamiento'] ?: null;
    $peso = $_POST['peso'] ?: null;
    $temperatura = $_POST['temperatura'] ?: null;
    $fc = $_POST['frecuencia_cardiaca'] ?: null;
    $fr = $_POST['frecuencia_respiratoria'] ?: null;
    $estado_animo = $_POST['estado_animo'] ?: null;
    $observaciones = $_POST['observaciones'] ?: null;
    $proximo_control = $_POST['proximo_control'] ?: null;

    $stmt = $conexion->prepare("UPDATE historias_clinicas SET 
        mascota_id=?, veterinario_id=?, fecha=?, motivo=?, diagnostico=?, tratamiento=?, peso=?, temperatura=?, frecuencia_cardiaca=?, frecuencia_respiratoria=?, estado_animo=?, observaciones=?, proximo_control=? 
        WHERE id=?");
    $stmt->bind_param("iissssddiiissi", $mascota_id, $veterinario_id, $fecha, $motivo, $diagnostico, $tratamiento, $peso, $temperatura, $fc, $fr, $estado_animo, $observaciones, $proximo_control, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: historias.php");
    exit;
}

// -------------------------
// ELIMINAR HISTORIA
// -------------------------
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conexion->prepare("DELETE FROM historias_clinicas WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: historias.php");
    exit;
}

// -------------------------
// PREPARAR EDICIÓN
// -------------------------
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM historias_clinicas WHERE id = $id LIMIT 1");
    if ($res && $res->num_rows) $editar = $res->fetch_assoc();
}

// -------------------------
// LISTAR HISTORIAS
// -------------------------
$listado = $conexion->query("
    SELECT h.*, 
           m.nombre AS nombre_mascota, c.nombre AS nombre_cliente, c.apellido AS apellido_cliente, 
           v.nombre AS nombre_veterinario, v.apellido AS apellido_veterinario
    FROM historias_clinicas h
    LEFT JOIN mascotas m ON h.mascota_id = m.id_mascota
    LEFT JOIN clientes c ON m.id_cliente = c.id_cliente
    LEFT JOIN veterinarios v ON h.veterinario_id = v.id_veterinario
    ORDER BY h.id DESC
");

// -------------------------
// LISTA DE MASCOTAS PARA EL SELECT
// -------------------------
$mascotas = $conexion->query("
    SELECT m.id_mascota, m.nombre AS nombre_mascota, c.nombre AS nombre_cliente, c.apellido AS apellido_cliente
    FROM mascotas m
    LEFT JOIN clientes c ON m.id_cliente = c.id_cliente
    ORDER BY c.nombre, m.nombre
");

// -------------------------
// LISTA DE VETERINARIOS PARA EL SELECT
// -------------------------
$veterinarios = $conexion->query("SELECT * FROM veterinarios ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historias Clínicas - Veterinaria Santiago Barrera</title>
<link rel="stylesheet" href="stylos.css">
<style>
/* Mantener diseño original exacto */
body { font-family: Arial, sans-serif; background:#f4f4f9; margin:0; padding:0; }
.header { display:flex; justify-content:space-between; align-items:center; padding:10px 30px; background:#ffb703; color:white; }
.header img { height:50px; }
.header nav a { margin:0 10px; color:white; text-decoration:none; font-weight:bold; }
.header nav a:hover { text-decoration:underline; }
.contenedor { max-width:950px; margin:20px auto; padding:20px; background:white; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2 { color:#023047; text-align:center; margin-top:0; }
input, select, textarea { width:100%; padding:10px; margin:6px 0; border-radius:8px; border:1px solid #ccc; font-size:16px; }
input:focus, select:focus, textarea:focus { border-color:#ffb703; outline:none; box-shadow:0 0 5px rgba(255,183,3,0.4);}
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
    <h2><?= $editar ? 'Editar Historia Clínica' : 'Nueva Historia Clínica' ?></h2>

    <form action="historias.php" method="POST" class="form-grid">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editar['id'] ?? '') ?>">

        <label>Mascota</label>
        <select name="mascota_id" required>
            <option value="">Seleccione Mascota</option>
            <?php while($m = $mascotas->fetch_assoc()): ?>
                <option value="<?= $m['id_mascota'] ?>"
                    <?= ($editar && $editar['mascota_id']==$m['id_mascota']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nombre_mascota'] ?? '') ?> - <?= htmlspecialchars($m['nombre_cliente'] ?? '') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Veterinario</label>
        <select name="veterinario_id" required>
            <option value="">Seleccione Veterinario</option>
            <?php while($v = $veterinarios->fetch_assoc()): ?>
                <option value="<?= $v['id_veterinario'] ?>"
                    <?= ($editar && $editar['veterinario_id']==$v['id_veterinario']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['nombre'] ?? '') ?> <?= htmlspecialchars($v['apellido'] ?? '') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input type="date" name="fecha" placeholder="dd/mm/aaaa" required value="<?= htmlspecialchars($editar['fecha'] ?? '') ?>">
        <input type="text" name="motivo" placeholder="Motivo de Consulta" required value="<?= htmlspecialchars($editar['motivo'] ?? '') ?>">
        <input type="text" name="diagnostico" placeholder="Diagnóstico" value="<?= htmlspecialchars($editar['diagnostico'] ?? '') ?>">
        <input type="text" name="tratamiento" placeholder="Tratamiento" value="<?= htmlspecialchars($editar['tratamiento'] ?? '') ?>">
        <input type="number" step="0.01" name="peso" placeholder="Peso (kg)" value="<?= htmlspecialchars($editar['peso'] ?? '') ?>">
        <input type="number" step="0.1" name="temperatura" placeholder="Temperatura (°C)" value="<?= htmlspecialchars($editar['temperatura'] ?? '') ?>">
        <input type="number" name="frecuencia_cardiaca" placeholder="Frecuencia cardíaca" value="<?= htmlspecialchars($editar['frecuencia_cardiaca'] ?? '') ?>">
        <input type="number" name="frecuencia_respiratoria" placeholder="Frecuencia respiratoria" value="<?= htmlspecialchars($editar['frecuencia_respiratoria'] ?? '') ?>">
        <input type="text" name="estado_animo" placeholder="Estado de ánimo" value="<?= htmlspecialchars($editar['estado_animo'] ?? '') ?>">
        <textarea name="observaciones" placeholder="Observaciones"><?= htmlspecialchars($editar['observaciones'] ?? '') ?></textarea>
        <label>Próximo Control</label>
        <input type="date" name="proximo_control" value="<?= htmlspecialchars($editar['proximo_control'] ?? '') ?>">

        <button type="submit" name="<?= $editar ? 'editar' : 'guardar' ?>" class="btn full"><?= $editar ? 'Actualizar' : '➕ Guardar' ?></button>
    </form>
</div>

<div class="contenedor">
    <h2>Lista de Historias Clínicas</h2>

    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mascota</th>
                <th>Cliente</th>
                <th>Veterinario</th>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Diagnóstico</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while($h = $listado->fetch_assoc()): ?>
            <tr class="fila">
                <td><?= htmlspecialchars($h['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($h['nombre_mascota'] ?? '') ?></td>
                <td><?= htmlspecialchars(($h['nombre_cliente'] ?? '').' '.($h['apellido_cliente'] ?? '')) ?></td>
                <td><?= htmlspecialchars(($h['nombre_veterinario'] ?? '').' '.($h['apellido_veterinario'] ?? '')) ?></td>
                <td><?= htmlspecialchars($h['fecha'] ?? '') ?></td>
                <td><?= htmlspecialchars($h['motivo'] ?? '') ?></td>
                <td><?= htmlspecialchars($h['diagnostico'] ?? '') ?></td>
                <td>
                    <a href="historias.php?editar=<?= $h['id'] ?>" class="btn-tabla editar">✏ Editar</a>
                    <a href="historias.php?eliminar=<?= $h['id'] ?>" class="btn-tabla eliminar" onclick="return confirm('¿Eliminar historia clínica?')">🗑 Eliminar</a>
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
