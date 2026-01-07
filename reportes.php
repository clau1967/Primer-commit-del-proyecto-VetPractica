<?php
// ===============================
// CONEXIÓN BD (DIRECTA - NO TOCAR)
// ===============================
$conn = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ===============================
// FILTRO POR FECHAS
// ===============================
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$filtro_fecha = "";
if ($fecha_inicio && $fecha_fin) {
    $filtro_fecha = " AND c.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' ";
}

// ===============================
// MASCOTAS ATENDIDAS POR VET
// ===============================
$atendidas_por_vet = $conn->query("
    SELECT 
        v.id_veterinario,
        v.nombre,
        v.apellido,
        COUNT(DISTINCT c.id_cita) AS total_atendidas
    FROM citas c
    JOIN veterinarios v ON c.id_veterinario = v.id_veterinario
    WHERE c.estado = 'atendida'
    $filtro_fecha
    GROUP BY v.id_veterinario, v.nombre, v.apellido
    ORDER BY total_atendidas DESC
");

// ===============================
// MASCOTAS SIN REVISIÓN
// ===============================
$sin_revision = $conn->query("
    SELECT COUNT(*) AS total
    FROM citas c
    WHERE c.estado <> 'atendida'
    $filtro_fecha
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="stylos.css">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- ===============================
 HEADER
================================ -->
<header class="header">
    <div class="header-left">
        <img src="img/logo.png" alt="Veterinaria Santiago Barrera">
        <span class="header-title">Veterinaria Santiago Barrera</span>
    </div>

    <nav class="header-nav">
        <a href="index.php">Inicio</a>
        <a href="clientes.php">Clientes</a>
        <a href="mascotas.php">Mascotas</a>
        <a href="citas.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php" class="active">Reportes</a>
    </nav>
</header>

<!-- ===============================
 CONTENIDO
================================ -->
<main class="flex-fill container my-4">

<div class="title-vet mb-4">
    <h4>📊 Reportes Generales</h4>
</div>

<!-- FILTRO FECHA -->
<div class="card mb-4 shadow-sm">
<div class="card-body">
<h6 class="mb-3">📅 Filtro por rango de fechas</h6>

<form method="GET" class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Desde</label>
        <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Hasta</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end gap-2">
        <button class="btn btn-primary">Filtrar</button>
        <a href="reportes.php" class="btn btn-secondary">Limpiar</a>
    </div>
</form>
</div>
</div>

<!-- ATENDIDAS POR VET -->
<div class="card mb-4 shadow-sm">
<div class="card-body">
<h6 class="mb-3">👨‍⚕️ Mascotas atendidas por veterinario</h6>

<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
    <th>Veterinario</th>
    <th>Total atendidas</th>
</tr>
</thead>
<tbody>
<?php if ($atendidas_por_vet && $atendidas_por_vet->num_rows > 0): ?>
    <?php while ($r = $atendidas_por_vet->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($r['nombre'].' '.$r['apellido']) ?></td>
        <td class="fw-bold"><?= $r['total_atendidas'] ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="2" class="text-center text-muted">
            No hay datos para el rango seleccionado
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- SIN REVISION -->
<div class="card shadow-sm">
<div class="card-body">
<h6 class="mb-3">⚠️ Mascotas sin revisión</h6>

<p class="fs-5">
Total:
<span class="fw-bold text-danger">
<?= $sin_revision['total'] ?? 0 ?>
</span>
</p>
</div>
</div>

</main>

<!-- ===============================
 FOOTER OFICIAL (NO TOCADO)
================================ -->
<footer class="footer-vet mt-auto">
<div class="container text-center">
<p class="fw-semibold mb-1">🐾 Veterinaria Santiago Barrera</p>
<p class="text-muted mb-2">Cuidado profesional y amor para tus mascotas</p>
<div class="d-flex justify-content-center gap-3 mb-2">
<span>🟢 WhatsApp: 317 680 1793</span>
<span>📸 Instagram: @santiagobarreraveterinario</span>
</div>
<small class="text-muted">© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados</small>
</div>
</footer>

</body>
</html>
