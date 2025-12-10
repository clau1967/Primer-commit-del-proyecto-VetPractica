<?php
// Mostrar errores solo en desarrollo (puedes comentar en producción)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Evitar que MySQLi convierta errores en excepciones fatales
mysqli_report(MYSQLI_REPORT_OFF);

include __DIR__ . "/backend/conectar.php";

session_start();

$mensaje = "";

// Si no existe la conexión, mostrar mensaje claro
if (!isset($conexion) || !$conexion) {
    die("Error: no se pudo conectar a la base de datos. Verifica backend/conectar.php");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Campos que deben coincidir con register.php
    $correo     = trim($_POST['correo'] ?? "");
    $contrasena = $_POST['contrasena'] ?? "";

    if ($correo === "" || $contrasena === "") {
        $mensaje = "Por favor completa todos los campos.";
    } else {

        // Buscar usuario exacto por username (tu columna)
        $sql = "SELECT id_usuario, username, password, rol FROM usuarios WHERE username = ?";
        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            $mensaje = "Error en la consulta: " . htmlspecialchars($conexion->error);
        } else {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado && $resultado->num_rows === 1) {

                $user = $resultado->fetch_assoc();
                $stored = $user['password']; // puede ser hash o texto plano

                // 1) Si es hash válido -> password_verify
                if (password_verify($contrasena, $stored)) {

                    // Rehash si es necesario
                    if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                        $nuevo_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $u = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                        if ($u) {
                            $u->bind_param("si", $nuevo_hash, $user['id_usuario']);
                            $u->execute();
                            $u->close();
                        }
                    }

                    // Login correcto
                    $_SESSION['usuario'] = $user['username'];
                    $_SESSION['rol'] = $user['rol'];

                    $stmt->close();
                    header("Location: bienvenida.php");
                    exit();

                } else {
                    // 2) Si no pasa password_verify, comprobar si stored es texto plano idéntico
                    if ($contrasena === $stored) {
                        // Actualizar a hash y permitir login
                        $nuevo_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $u = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                        if ($u) {
                            $u->bind_param("si", $nuevo_hash, $user['id_usuario']);
                            if ($u->execute()) {
                                // sesión y login
                                $_SESSION['usuario'] = $user['username'];
                                $_SESSION['rol'] = $user['rol'];
                                $u->close();
                                $stmt->close();
                                header("Location: bienvenida.php");
                                exit();
                            } else {
                                $mensaje = "Error al actualizar la contraseña: " . htmlspecialchars($u->error);
                                $u->close();
                            }
                        } else {
                            $mensaje = "Error al preparar actualización: " . htmlspecialchars($conexion->error);
                        }
                    } else {
                        $mensaje = "Contraseña incorrecta ❌";
                    }
                }

            } else {
                $mensaje = "El usuario no existe ❌";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Veterinaria Santiago Barrera — Iniciar sesión</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
    :root{--amarillo:#fff9c4;--rosa:#e91e63;--boton:#fdd835;}
    body{margin:0;font-family:Arial, sans-serif;background:var(--amarillo);color:#222}
    .wrap{display:flex;justify-content:center;align-items:center;min-height:100vh;flex-direction:column;}
    .card{background:#fff;padding:30px;border-radius:10px;box-shadow:0 0 12px rgba(0,0,0,0.15);width:360px;max-width:90%;text-align:center;}
    .logo{width:100px;height:auto;margin-bottom:12px;display:block;margin-left:auto;margin-right:auto;}
    h1{font-size:18px;color:var(--rosa);margin:6px 0 14px;font-weight:700;}
    p.lead{margin:0 0 14px;color:#555;font-size:14px;}
    input[type="email"],input[type="password"]{width:100%;padding:10px;margin:8px 0;border-radius:6px;border:1px solid #ccc;box-sizing:border-box;}
    button{width:100%;padding:12px;margin-top:12px;border-radius:6px;border:none;background:var(--boton);font-weight:700;cursor:pointer;}
    .link-btn{display:inline-block;margin-top:12px;padding:10px 18px;border-radius:6px;background:var(--rosa);color:#fff;text-decoration:none;font-weight:700;}
    .msg{margin-top:12px;color:#c62828;font-weight:700;}
    footer{margin-top:26px;width:100%;background:#333;color:#fff;padding:14px 0;text-align:center;font-size:13px;}
    footer a{color:#fff;text-decoration:none}
</style>
</head>
<body>

<div class="wrap">
    <div class="card">
        <img src="img/logo.png" alt="Logo Veterinaria" class="logo">
        <h1>Estás en la Veterinaria Santiago Barrera</h1>
        <p class="lead">Por favor ingresa tus credenciales</p>

        <?php if ($mensaje !== ""): ?>
            <div class="msg"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off" novalidate>
            <input type="email" name="correo" placeholder="Correo" autocomplete="off" value="" required>
            <input type="password" name="contrasena" placeholder="Contraseña" autocomplete="new-password" value="" required>
            <button type="submit">Ingresar</button>
        </form>

        <a class="link-btn" href="register.php">¿No tienes cuenta? Regístrate</a>
    </div>

    <footer>
        <p>© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados.</p>
        <p>
            🟢 <a href="https://wa.me/573176801793" target="_blank">WhatsApp 3176801793</a> |
            <a href="https://www.instagram.com/santiagobarreraveterinario" target="_blank">@santiagobarreraveterinario</a>
        </p>
    </footer>
</div>

</body>
</html>
