<?php
session_start();

// Si no está logueada, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenida - Veterinaria Santiago Barrera</title>

    <style>
        body {
            background-color: #f8f3c8;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;

            /* Para que el footer no quede tan lejos */
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 30px;
        }

        .container {
            background: white;
            width: 380px;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            margin-top: 10px;
            color: #444;
        }

        .btn {
            display: block;
            width: 100%;
            background: #f5b335;
            color: #000;
            padding: 12px;
            margin: 8px 0;
            border-radius: 10px;
            font-size: 16px;
            text-decoration: none;
            font-weight: bold;

            /* Hover */
            transition: 0.25s ease;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.15);
        }

        .btn:hover {
            background: #ffca47;
            transform: translateY(-2px);
            box-shadow: 0px 5px 14px rgba(0,0,0,0.25);
        }

        .logout {
            margin-top: 10px;
            color: red;
            font-weight: bold;
            text-decoration: none;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #444;
            margin-top: auto;
        }

        footer a {
            color: #444;
            text-decoration: none;
            font-weight: bold;
        }
    </style>

</head>
<body>

    <div class="container">
        <img src="img/logo.png" width="170" alt="Logo">
        <h2>Bienvenida</h2>

        <a class="btn" href="clientes.php">👤 Clientes</a>
        <a class="btn" href="mascotas.php">🐾 Mascotas</a>
        <a class="btn" href="cita.php">📅 Citas</a>
        <a class="btn" href="veterinarios.php">👨‍⚕️ Veterinarios</a>
        <a class="btn" href="consultorios.php">🩺 Consultorios</a>
        <a class="btn" href="historias.php">📋 Historias Clínicas</a>
        <a class="btn" href="formulas.php">💊 Fórmulas</a>
        <a class="btn" href="reportes.php">📊 Reportes</a>

        <a class="logout" href="logout.php">Cerrar sesión</a>
    </div>

   <footer>
        <p>© 2025 Veterinaria Santiago Barrera — Todos los derechos reservados.</p>
        <p>
            🟢 <a href="https://wa.me/573176801793" target="_blank">WhatsApp 3176801793</a> |
            <a href="https://www.instagram.com/santiagobarreraveterinario" target="_blank">@santiagobarreraveterinario</a>
        </p>
    </footer>


</body>
</html>
