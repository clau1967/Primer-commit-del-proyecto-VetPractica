<?php
// ===============================
// LOGIN PROFESIONAL - Veterinaria Santiago Barrera
// ===============================

mysqli_report(MYSQLI_REPORT_OFF);
include __DIR__ . "/backend/conectar.php";
session_start();

$mensaje = "";

if (!isset($conexion) || !$conexion) {
    die("Error: no se pudo conectar a la base de datos.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo     = trim($_POST['correo'] ?? "");
    $contrasena = $_POST['contrasena'] ?? "";

    if ($correo === "" || $contrasena === "") {
        $mensaje = "Por favor completa todos los campos.";
    } else {
        $sql = "SELECT id_usuario, username, password, rol FROM usuarios WHERE username = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado && $resultado->num_rows === 1) {
                $user = $resultado->fetch_assoc();
                $stored = $user['password'];

                if (password_verify($contrasena, $stored) || $contrasena === $stored) {
                    if ($contrasena === $stored) {
                        $nuevo_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $u = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                        if ($u) {
                            $u->bind_param("si", $nuevo_hash, $user['id_usuario']);
                            $u->execute();
                            $u->close();
                        }
                    }
                    $_SESSION['usuario'] = $user['username'];
                    $_SESSION['rol']     = $user['rol'];
                    header("Location: bienvenida.php");
                    exit;
                } else {
                    $mensaje = "Contraseña incorrecta ❌";
                }
            } else {
                $mensaje = "El usuario no existe ❌";
            }
            $stmt->close();
        } else {
            $mensaje = "Error en la consulta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al Sistema — Veterinaria Santiago Barrera</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        :root {
            --primario: #fdd835;
            --acento: #e91e63;
            --texto: #333;
            --gris-suave: #f8fafc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #fff; height: 100vh; overflow: hidden; }

        .login-wrapper { display: flex; height: 100vh; }

        .side-image {
            flex: 1.2;
            background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1)), 
                        url('img/portada_login.jpg'); 
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            text-align: center;
        }

        .side-form {
            flex: 0.8;
            display: flex;
            flex-direction: column;
            background: #fff;
            position: relative;
        }

        .form-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        /* Contenedor más ancho para soportar elementos más grandes */
        .form-container { width: 100%; max-width: 500px; text-align: center; }
        .logo { width: 160px; margin-bottom: 30px; }
        
        h1 { font-size: 42px; color: var(--texto); font-weight: 700; margin-bottom: 10px; }
        .lead-text { color: #666; font-size: 24px; margin-bottom: 40px; }

        .input-box { position: relative; margin-bottom: 25px; }
        
        /* Iconos XL */
        .input-box span { 
            position: absolute; 
            left: 20px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #999; 
            font-size: 40px; 
        } 
        
        /* INPUTS XL */
        input {
            width: 100%;
            padding: 25px 20px 25px 75px; 
            border: 2px solid #eee;
            border-radius: 18px;
            font-size: 26px; 
            transition: 0.3s;
            background: var(--gris-suave);
            font-weight: 400;
        }
        input:focus { border-color: var(--primario); outline: none; background: #fff; box-shadow: 0 0 20px rgba(253, 216, 53, 0.4); }

        /* BOTÓN XL */
        .btn-ingresar {
            width: 100%;
            padding: 22px;
            background: var(--primario);
            border: none;
            border-radius: 15px;
            font-weight: 700;
            font-size: 28px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 15px;
        }
        .btn-ingresar:hover { background: #fbc02d; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }

        /* REGISTRO XL */
        .btn-register {
            display: inline-block;
            margin-top: 35px;
            color: var(--acento);
            text-decoration: none;
            font-weight: 700;
            font-size: 22px;
        }

        .msg { background: #fff5f5; color: #c53030; padding: 20px; border-radius: 12px; margin-bottom: 25px; font-size: 18px; border-left: 6px solid #c53030; font-weight: 600; text-align: left; }

        .custom-footer {
            background: #fdfdfd;
            border-top: 1px solid #eee;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #555;
        }
        .footer-brand { font-weight: 700; color: #333; margin-bottom: 5px; font-size: 16px; }
        .footer-links a { color: #444; text-decoration: none; margin: 0 8px; font-weight: 600; }

        @media (max-width: 1000px) {
            .side-image { display: none; }
            body { overflow: auto; }
            .form-container { max-width: 90%; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="side-image"></div>

    <div class="side-form">
        <div class="form-content">
            <div class="form-container">
                <img src="img/logo.png" alt="Logo" class="logo">
                <h1>Acceso Administrativo</h1>
                <p class="lead-text">Ingresa tus credenciales para continuar</p>

                <?php if ($mensaje !== ""): ?>
                    <div class="msg"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-box">
                        <span class="material-symbols-outlined">person</span>
                        <input type="text" name="correo" placeholder="Usuario o correo" required>
                    </div>
                    
                    <div class="input-box">
                        <span class="material-symbols-outlined">lock</span>
                        <input type="password" name="contrasena" placeholder="Contraseña" required>
                    </div>

                    <button type="submit" class="btn-ingresar">Ingresar al Sistema</button>
                </form>

                <a class="btn-register" href="register.php">¿No tienes cuenta? Regístrate aquí  👈</a>
            </div>
        </div>

        <footer class="custom-footer">
            <div class="footer-brand">🐾 Veterinaria Santiago Barrera</div>
            <div style="margin-bottom: 8px;">Cuidado profesional y amor para tus mascotas</div>
            <div class="footer-links">
                <a href="https://wa.me/573176801793" target="_blank">🟢 WhatsApp: 317 680 1793</a> |
                <a href="https://www.instagram.com/santiagobarreraveterinario" target="_blank">📸 Instagram: @santiagobarreraveterinario</a>
            </div>
            <div style="font-size: 11px; color: #aaa; margin-top: 10px;">© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados</div>
        </footer>
    </div>
</div>

</body>
</html>