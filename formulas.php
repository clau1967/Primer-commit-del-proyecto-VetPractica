<?php include 'includes/header.php'; ?>

<?php
// formulas.php — Gestión CRUD de fórmulas

$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión: " . $conexion->connect_error);

// -------------------------
// GUARDAR FÓRMULA
// -------------------------
if (isset($_POST['guardar'])) {
    $descripcion         = $_POST['descripcion'];
    $dosis               = $_POST['dosis'];
    $indicaciones        = $_POST['indicaciones'];
    $medicamento         = $_POST['medicamento'];
    $historia_clinica_id = $_POST['historia_clinica_id'];

    $stmt = $conexion->prepare("INSERT INTO formulas (descripcion, dosis, indicaciones, medicamento, historia_clinica_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $descripcion, $dosis, $indicaciones, $medicamento, $historia_clinica_id);
    $stmt->execute();
    $stmt->close();

    header("Location: formulas.php");
    exit;
}

// -------------------------
// EDITAR FÓRMULA
// -------------------------
if (isset($_POST['editar'])) {
    $id                  = intval($_POST['id']);
    $descripcion         = $_POST['descripcion'];
    $dosis               = $_POST['dosis'];
    $indicaciones        = $_POST['indicaciones'];
    $medicamento         = $_POST['medicamento'];
    $historia_clinica_id = $_POST['historia_clinica_id'];

    $stmt = $conexion->prepare("UPDATE formulas SET descripcion=?, dosis=?, indicaciones=?, medicamento=?, historia_clinica_id=? WHERE id=?");
    $stmt->bind_param("ssssii", $descripcion, $dosis, $indicaciones, $medicamento, $historia_clinica_id, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: formulas.php");
    exit;
}

// -------------------------
// ELIMINAR FÓRMULA
// -------------------------
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conexion->prepare("DELETE FROM formulas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: formulas.php");
    exit;
}

// -------------------------
// PREPARAR EDICIÓN
// -------------------------
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conexion->query("SELECT * FROM formulas WHERE id = $id LIMIT 1");
    if ($res && $res->num_rows) $editar = $res->fetch_assoc();
}

// -------------------------
// LISTAR FÓRMULAS
// -------------------------
$formulas = $conexion->query("
    SELECT f.*, h.id AS historia_id, h.motivo,
           m.nombre AS mascota_nombre,
           c.nombre AS cliente_nombre, c.apellido AS cliente_apellido
    FROM formulas f
    JOIN historias_clinicas h ON f.historia_clinica_id = h.id
    JOIN mascotas m ON h.mascota_id = m.id_mascota
    JOIN clientes c ON m.id_cliente = c.id_cliente
    ORDER BY f.id DESC
");

// -------------------------
// LISTAR HISTORIAS PARA SELECT
// -------------------------
$historias = $conexion->query("
    SELECT h.id, m.nombre AS mascota_nombre,
           c.nombre AS cliente_nombre, c.apellido AS cliente_apellido,
           h.motivo
    FROM historias_clinicas h
    JOIN mascotas m ON h.mascota_id = m.id_mascota
    JOIN clientes c ON m.id_cliente = c.id_cliente
    ORDER BY h.id DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Fórmulas - Veterinaria Santiago Barrera</title>
<link rel="stylesheet" href="stylos.css">
<style>
body { font-family: Arial,sans-serif; background:#f4f4f9; margin:0; padding:0; }
.header { display:flex; justify-content:space-between; align-items:center; padding:10px 30px; background:#ffb703; color:white; }
.header img { height:50px; }
.header nav a { margin:0 10px; color:white; text-decoration:none; font-weight:bold; }
.header nav a:hover { text-decoration:underline; }
.contenedor { max-width:950px; margin:20px auto; padding:20px; background:white; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2 { color:#023047; text-align:center; margin-top:0; }
input, textarea, select { width:100%; padding:10px; margin:6px 0; border-radius:8px; border:1px solid #ccc; font-size:16px; }
input:focus, textarea:focus, select:focus { border-color:#ffb703; outline:none; box-shadow:0 0 5px rgba(255,183,3,0.4);}
.btn { padding:10px 18px; border:none; border-radius:8px; background:#ffb703; color:white; font-weight:bold; cursor:pointer; margin-top:10px; transition:0.3s;}
.btn:hover { background:#fb8500; }
.form-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:12px; }
.form-grid .full { grid-column: 1 / -1; }
.contenedor-tabla { max-width: 950px; overflow-x:auto; margin:0 auto; }
.tabla { width:100%; border-collapse: collapse; table-layout: fixed; }
.tabla th, .tabla td { border:1px solid #ddd; padding:12px; text-align:center; word-wrap:break-word; }
.tabla th { background:#ffb703; color:white; }
.fila:hover { background:#fef3d6; }
.btn-tabla { padding:6px 10px; border-radius:5px; color:white; text-decoration:none; margin:0 5px; display:inline-block;}
.editar { background:#219ebc; }
.eliminar { background:#e63946; }
@media (max-width:700px){ .form-grid { grid-template-columns:1fr; } }
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
    <h2>Agregar / Editar Fórmula</h2>

    <form action="formulas.php" method="POST" class="form-grid">
        <input type="hidden" name="id" value="<?= $editar ? htmlspecialchars($editar['id']) : '' ?>">

        <select name="historia_clinica_id" required class="full">
            <option value="">Seleccione Historia Clínica</option>
            <?php while($h = $historias->fetch_assoc()): ?>
                <option value="<?= $h['id'] ?>" <?= $editar && $editar['historia_clinica_id']==$h['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($h['cliente_nombre'].' '.$h['cliente_apellido'].' - '.$h['mascota_nombre'].' ('.$h['motivo'].')') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input type="text" name="descripcion" placeholder="Descripción" required value="<?= $editar ? htmlspecialchars($editar['descripcion']) : '' ?>">

        <!-- 🔴 CAMBIO ÚNICO: DOSIS LIBRE (NO SELECT) -->
        <input
            type="text"
            name="dosis"
            placeholder="Dosis (ej: 1 ml cada 12 horas por 7 días)"
            value="<?= $editar ? htmlspecialchars($editar['dosis']) : '' ?>"
        >

        <input type="text" name="indicaciones" placeholder="Indicaciones" value="<?= $editar ? htmlspecialchars($editar['indicaciones']) : '' ?>">
        <input type="text" name="medicamento" placeholder="Medicamento" value="<?= $editar ? htmlspecialchars($editar['medicamento']) : '' ?>">

        <button type="submit" name="<?= $editar ? 'editar' : 'guardar' ?>" class="btn full">
            <?= $editar ? 'Actualizar' : '➕ Guardar' ?>
        </button>
    </form>
</div>

<div class="contenedor-tabla">
    <h2>Lista de Fórmulas</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Historia</th>
                <th>Cliente</th>
                <th>Mascota</th>
                <th>Motivo</th>
                <th>Descripción</th>
                <th>Dosis</th>
                <th>Indicaciones</th>
                <th>Medicamento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($f = $formulas->fetch_assoc()): ?>
            <tr class="fila">
                <td><?= htmlspecialchars($f['id']) ?></td>
                <td><?= htmlspecialchars($f['historia_id']) ?></td>
                <td><?= htmlspecialchars($f['cliente_nombre'].' '.$f['cliente_apellido']) ?></td>
                <td><?= htmlspecialchars($f['mascota_nombre']) ?></td>
                <td><?= htmlspecialchars($f['motivo']) ?></td>
                <td><?= htmlspecialchars($f['descripcion']) ?></td>
                <td><?= htmlspecialchars($f['dosis']) ?></td>
                <td><?= htmlspecialchars($f['indicaciones']) ?></td>
                <td><?= htmlspecialchars($f['medicamento']) ?></td>
                <td>
                    <a href="formulas.php?editar=<?= $f['id'] ?>" class="btn-tabla editar">✏ Editar</a>
                    <a href="formulas.php?eliminar=<?= $f['id'] ?>" class="btn-tabla eliminar" onclick="return confirm('¿Eliminar fórmula?')">🗑 Eliminar</a>
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


<?php include 'includes/footer.php'; ?>