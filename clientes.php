<?php
// ===============================
// CONEXIÓN BD
// ===============================
$conn = new mysqli("localhost", "vetsantiago", "veterinaria123", "veterinaria");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// ===============================
// VARIABLES
// ===============================
$id_cliente = "";
$nombre = "";
$apellido = "";
$correo = "";
$telefono = "";
$direccion = "";
$mensaje_error = "";

// ===============================
// GUARDAR / ACTUALIZAR
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_cliente = $_POST["id_cliente"] ?? "";
    $nombre     = $_POST["nombre"];
    $apellido   = $_POST["apellido"];
    $correo     = $_POST["correo"];
    $telefono   = $_POST["telefono"];
    $direccion  = $_POST["direccion"];

    if ($id_cliente == "") {

        // VALIDAR CORREO DUPLICADO
        $check = $conn->prepare("SELECT id_cliente FROM clientes WHERE correo=?");
        $check->bind_param("s", $correo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje_error = "⚠️ El correo ya está registrado en otro cliente.";
        } else {
            // INSERT
            $stmt = $conn->prepare(
                "INSERT INTO clientes (nombre, apellido, correo, telefono, direccion)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $nombre, $apellido, $correo, $telefono, $direccion);
            $stmt->execute();

            header("Location: clientes.php");
            exit;
        }

    } else {

        // UPDATE
        $stmt = $conn->prepare(
            "UPDATE clientes 
             SET nombre=?, apellido=?, correo=?, telefono=?, direccion=?
             WHERE id_cliente=?"
        );
        $stmt->bind_param("sssssi", $nombre, $apellido, $correo, $telefono, $direccion, $id_cliente);
        $stmt->execute();

        header("Location: clientes.php");
        exit;
    }
}

// ===============================
// EDITAR (PRECARGA)
// ===============================
if (isset($_GET["editar"])) {
    $id_cliente = $_GET["editar"];

    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente=?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $nombre    = $row["nombre"];
        $apellido  = $row["apellido"];
        $correo    = $row["correo"];
        $telefono  = $row["telefono"];
        $direccion = $row["direccion"];
    }
}

// ===============================
// ELIMINAR
// ===============================
if (isset($_GET["eliminar"])) {
    $id = $_GET["eliminar"];

    $stmt = $conn->prepare("DELETE FROM clientes WHERE id_cliente=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: clientes.php");
    exit;
}

// ===============================
// LISTADO
// ===============================
$clientes = $conn->query("SELECT * FROM clientes ORDER BY id_cliente DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Clientes</title>

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
        <a href="clientes.php" class="active">Clientes</a>
        <a href="mascotas.php">Mascotas</a>
        <a href="citas.php">Citas</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<!-- ===============================
 CONTENIDO
================================ -->
<main class="flex-fill container my-4">

    <!-- TÍTULO MEJORADO -->
  <div class="title-vet">
    <div>
        <h4>Gestión de Clientes</h4>
    </div>
  </div>


    <!-- FORMULARIO -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3 text-primary">
                <?php echo $id_cliente ? "Editar Cliente" : "Registrar Cliente"; ?>
            </h6>

            <?php if ($mensaje_error): ?>
                <div class="alert alert-warning">
                    <?php echo $mensaje_error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?php echo $nombre; ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required value="<?php echo $apellido; ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" value="<?php echo $correo; ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?php echo $telefono; ?>">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?php echo $direccion; ?>">
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-success">💾 Guardar</button>
                    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTADO -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">📋 Lista de Clientes</h6>

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($c = $clientes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $c["id_cliente"]; ?></td>
                        <td><?php echo $c["nombre"] . " " . $c["apellido"]; ?></td>
                        <td><?php echo $c["correo"]; ?></td>
                        <td><?php echo $c["telefono"]; ?></td>
                        <td><?php echo $c["direccion"]; ?></td>
                        <td class="text-center">
                            <a href="clientes.php?editar=<?php echo $c["id_cliente"]; ?>" class="btn-action btn-edit">✏️ Editar</a>
                            <a href="clientes.php?eliminar=<?php echo $c["id_cliente"]; ?>"
                               class="btn-action btn-delete"
                               onclick="return confirm('¿Eliminar este cliente?')">🗑 Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ===============================
 FOOTER OFICIAL
================================ -->
<footer class="footer-vet mt-auto">
    <div class="container text-center">
        <p class="fw-semibold mb-1">
            🐾 Veterinaria Santiago Barrera
        </p>
        <p class="text-muted mb-2">
            Cuidado profesional y amor para tus mascotas
        </p>

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
