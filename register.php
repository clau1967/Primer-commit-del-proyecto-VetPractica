<?php
require_once "backend/conectar.php";

$mensaje = "";
$exito = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo     = trim($_POST["correo"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");

    if ($correo === "" || $contrasena === "") {
        $mensaje = "Todos los campos son obligatorios ❌";
    } else {

        // Encriptar contraseña
        $passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (username, password, rol)
                VALUES (?, ?, 'TUTOR')";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $correo, $passwordHash);

        if ($stmt->execute()) {
            $exito = true;
            $mensaje = "Registro exitoso. Ahora inicia sesión 😊";
        } else {
            if ($conexion->errno == 1062) {
                $mensaje = "Este correo ya está registrado ❌";
            } else {
                $mensaje = "Error al registrar: " . $conexion->error;
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro</title>
</head>

<body style="background:#faf3c0; margin:0; font-family:sans-serif;">

<div style="display:flex; justify-content:center; align-items:center; min-height:100vh;">

    <div style="background:#fff; padding:30px; border-radius:10px; width:380px; box-shadow:0 0 12px rgba(0,0,0,0.2);">

        <img src="img/logo.png" style="width:100px; display:block; margin:auto;">

        <h2 style="text-align:center; color:#e91e63;">
            Crear cuenta
        </h2>

        <?php if($mensaje !== ""): ?>
            <div style="margin-bottom:15px; color:#c62828; font-weight:bold;">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="email" name="correo" placeholder="Correo electrónico" required
                   style="width:100%; padding:10px; margin:10px 0; border-radius:5px;">

            <input type="password" name="contrasena" placeholder="Contraseña" required
                   style="width:100%; padding:10px; margin:10px 0; border-radius:5px;">

            <button type="submit" style="width:100%; padding:12px; background:#fdd835; border:none; border-radius:5px; font-weight:bold;">
                Registrarme
            </button>
        </form>

        <p style="text-align:center; margin-top:15px;">
            ¿Ya tienes cuenta?  
            <a href="login.php" style="color:#e91e63; font-weight:bold;">Inicia sesión</a>
        </p>

    </div>
</div>

</body>
</html>
