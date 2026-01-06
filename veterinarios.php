<?php
// ===============================
// CONEXIÓN BD (MISMA QUE CLIENTES)
// ===============================
$conn = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// ===============================
// VARIABLES
// ===============================
$id_veterinario = "";
$nombre = "";
$apellido = "";
$especialidad = "";
$telefono = "";
$correo = "";

// ===============================
// GUARDAR / ACTUALIZAR
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_veterinario = $_POST["id_veterinario"] ?? "";
    $nombre         = $_POST["nombre"];
    $apellido       = $_POST["apellido"];
    $especialidad   = $_POST["especialidad"];
    $telefono       = $_POST["telefono"];
    $correo         = $_POST["correo"];

    if ($id_veterinario == "") {

        // INSERT
        $stmt = $conn->prepare(
            "INSERT INTO veterinarios 
            (nombre, apellido, especialidad, telefono, correo)
            VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $nombre, $apellido, $especialidad, $telefono, $correo);
        $stmt->execute();

        header("Location: veterinarios.php");
        exit;

    } else {

        // UPDATE
        $stmt = $conn->prepare(
            "UPDATE veterinarios SET
                nombre=?,
                apellido=?,
                especialidad=?,
                telefono=?,
                correo=?
             WHERE id_veterinario=?"
        );
        $stmt->bind_param(
            "sssssi",
            $nombre,
            $apellido,
            $especialidad,
            $telefono,
            $correo,
            $id_veterinario
        );
        $stmt->execute();

        header("Location: veterinarios.php");
        exit;
    }
}

// ===============================
// EDITAR (PRECARGA)
// ===============================
if (isset($_GET["editar"])) {
    $id_veterinario = $_GET["editar"];

    $stmt = $conn->prepare("SELECT * FROM veterinarios WHERE id_veterinario=?");
    $stmt->bind_param("i", $id_veterinario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $nombre       = $row["nombre"];
        $apellido     = $row["apellido"];
        $especialidad = $row["especialidad"];
        $telefono     = $row["telefono"];
        $correo       = $row["correo"];
    }
}

// ===============================
// ELIMINAR
// ===============================
if (isset($_GET["eliminar"])) {
    $id = $_GET["eliminar"];

    $stmt = $conn->prepare("DELETE FROM veterinarios WHERE id_veterinario=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: veterinarios.php");
    exit;
}

// ===============================
// LISTADO
// ===============================
$veterinarios = $conn->query(
    "SELECT * FROM veterinarios ORDER BY id_veterinario DESC"
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Veterinarios</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="stylos.css">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- ===============================
 HEADER (IDÉNTICO A CLIENTES)
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
        <a href="veterinarios.php" class="active">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<!-- ===============================
 CONTENIDO
================================ -->
<main class="flex-fill container my-4">

    <div class="title-vet">
        <h4>Gestión de Veterinarios</h4>
    </div>

    <!-- FORMULARIO -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3 text-primary">
                <?php echo $id_veterinario ? "Editar Veterinario" : "Registrar Veterinario"; ?>
            </h6>

            <form method="POST">
                <input type="hidden" name="id_veterinario" value="<?php echo $id_veterinario; ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?php echo $nombre; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required value="<?php echo $apellido; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Especialidad</label>
                        <input type="text" name="especialidad" class="form-control" value="<?php echo $especialidad; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?php echo $telefono; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" value="<?php echo $correo; ?>">
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-success">💾 Guardar</button>
                    <a href="veterinarios.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTADO -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">📋 Lista de Veterinarios</h6>

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Veterinario</th>
                        <th>Especialidad</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($v = $veterinarios->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $v["id_veterinario"]; ?></td>
                        <td><?php echo $v["nombre"] . " " . $v["apellido"]; ?></td>
                        <td><?php echo $v["especialidad"]; ?></td>
                        <td><?php echo $v["telefono"]; ?></td>
                        <td><?php echo $v["correo"]; ?></td>
                        <td class="text-center">
                            <a href="veterinarios.php?editar=<?php echo $v["id_veterinario"]; ?>" class="btn-action btn-edit">✏️ Editar</a>
                            <a href="veterinarios.php?eliminar=<?php echo $v["id_veterinario"]; ?>"
                               class="btn-action btn-delete"
                               onclick="return confirm('¿Eliminar este veterinario?')">🗑 Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ===============================
 FOOTER (IDÉNTICO A CLIENTES)
================================ -->
<footer class="footer-vet mt-auto">
    <div class="container text-center">
        <p class="fw-semibold mb-1">🐾 Veterinaria Santiago Barrera</p>
        <p class="text-muted mb-2">Cuidado profesional y amor para tus mascotas</p>

        <div class="d-flex justify-content-center gap-3 mb-2">
            <span>🟢 WhatsApp: 317 680 1793</span>
            <span>📸 Instagram: @santiagobarreraveterinario</span>
        </div>

        <small class="text-muted">
            © 2025 Veterinaria Santiago Barrera — Todos los derechos reservados
        </small>
    </div>
</footer>

</body>
</html>
