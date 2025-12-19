<?php include 'includes/header.php'; ?>

<?php
// reportes.php — Listado de Historias Clínicas con diseño funcional y columnas ajustadas

$conexion = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conexion->connect_errno) die("Error de conexión: " . $conexion->connect_error);

// Obtener todas las historias con información de mascotas, clientes y veterinarios
$query = "
SELECT 
    h.*,
    m.nombre AS mascota_nombre,
    c.nombre AS cliente_nombre,
    c.apellido AS cliente_apellido,
    v.nombre AS veterinario_nombre,
    v.apellido AS veterinario_apellido
FROM historias_clinicas h
LEFT JOIN mascotas m ON h.mascota_id = m.id_mascota
LEFT JOIN clientes c ON m.id_cliente = c.id_cliente
LEFT JOIN veterinarios v ON h.veterinario_id = v.id_veterinario
ORDER BY h.id DESC
";

$historias = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes - Historias Clínicas</title>
<link rel="stylesheet" href="stylos.css">
<style>
body { font-family: Arial, sans-serif; background:#f4f4f9; margin:0; padding:0; }
.header { display:flex; justify-content:space-between; align-items:center; padding:10px 30px; background:#ffb703; color:white; }
.header img { height:50px; }
.header nav a { margin:0 10px; color:white; text-decoration:none; font-weight:bold; }
.header nav a:hover { text-decoration:underline; }

.contenedor { max-width:1200px; margin:20px auto; padding:20px; background:white; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
.contenedor-tabla { overflow-x:auto; }

.tabla { width:100%; border-collapse: collapse; table-layout: fixed; }
.tabla th, .tabla td { border:1px solid #ddd; padding:10px; text-align:center; word-wrap:break-word; font-size:14px; }

/* Anchos por columna */
.tabla th.id, .tabla td.id { width:40px; }
.tabla th.mascota, .tabla td.mascota { width:130px; }
.tabla th.cliente, .tabla td.cliente { width:150px; }
.tabla th.veterinario, .tabla td.veterinario { width:150px; }
.tabla th.fecha, .tabla td.fecha { width:90px; }
.tabla th.motivo, .tabla td.motivo { width:150px; }
.tabla th.diagnostico, .tabla td.diagnostico { width:150px; }
.tabla th.tratamiento, .tabla td.tratamiento { width:150px; }
.tabla th.proximo, .tabla td.proximo { width:90px; }

/* columnas secundarias numéricas más pequeñas */
.tabla .secundaria { width:60px; }

/* hover */
.fila:hover { background:#fef3d6; }

h2 { color:#023047; text-align:center; margin-top:0; }
.btn { padding:10px 18px; border:none; border-radius:8px; background:#ffb703; color:white; font-weight:bold; cursor:pointer; margin-top:10px; transition:0.3s;}
.btn:hover { background:#fb8500; }

@media (max-width:1200px) { .tabla .secundaria { display:none; } }
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
    <h2>Reportes de Historias Clínicas</h2>

    <div class="contenedor-tabla">
    <table class="tabla">
        <thead>
            <tr>
                <th class="id">ID</th>
                <th class="mascota">Mascota</th>
                <th class="cliente">Cliente</th>
                <th class="veterinario">Veterinario</th>
                <th class="fecha">Fecha</th>
                <th class="motivo">Motivo</th>
                <th class="diagnostico">Diagnóstico</th>
                <th class="tratamiento">Tratamiento</th>
                <th class="secundaria">Peso</th>
                <th class="secundaria">Temp</th>
                <th class="secundaria">FC</th>
                <th class="secundaria">FR</th>
                <th class="secundaria">Estado de ánimo</th>
                <th class="secundaria">Observaciones</th>
                <th class="proximo">Próximo Control</th>
            </tr>
        </thead>
        <tbody>
        <?php while($h = $historias->fetch_assoc()): ?>
            <tr class="fila">
                <td class="id"><?= htmlspecialchars($h['id'] ?? '') ?></td>
                <td class="mascota"><?= htmlspecialchars($h['mascota_nombre'] ?? '') ?></td>
                <td class="cliente"><?= htmlspecialchars(($h['cliente_nombre'] ?? '').' '.($h['cliente_apellido'] ?? '')) ?></td>
                <td class="veterinario"><?= htmlspecialchars(($h['veterinario_nombre'] ?? '').' '.($h['veterinario_apellido'] ?? '')) ?></td>
                <td class="fecha"><?= htmlspecialchars($h['fecha'] ?? '') ?></td>
                <td class="motivo"><?= htmlspecialchars($h['motivo'] ?? '') ?></td>
                <td class="diagnostico"><?= htmlspecialchars($h['diagnostico'] ?? '') ?></td>
                <td class="tratamiento"><?= htmlspecialchars($h['tratamiento'] ?? '') ?></td>
                <td class="secundaria"><?= htmlspecialchars($h['peso'] ?? '') ?></td>
                <td class="secundaria"><?= htmlspecialchars($h['temperatura'] ?? '') ?></td>
                <td class="secundaria"><?= htmlspecialchars($h['frecuencia_cardiaca'] ?? '') ?></td>
                <td class="secundaria"><?= htmlspecialchars($h['frecuencia_respiratoria'] ?? '') ?></td>
                <td class="secundaria"><?= htmlspecialchars($h['estado_animo'] ?? '') ?></td>
                <td class="secundaria"><?= htmlspecialchars($h['observaciones'] ?? '') ?></td>
                <td class="proximo"><?= htmlspecialchars($h['proximo_control'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
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