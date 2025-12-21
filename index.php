<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Veterinaria Santiago Barrera - Inicio</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS global (Clientes como base) -->
    <link rel="stylesheet" href="stylos.css">

    <style>
        /* ===============================
           Tarjetas de acceso a entidades
        =============================== */
        .quick-access {
            background: #fff8e1; /* amarillo suave */
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
            text-decoration: none;
            color: #333;
        }

        .quick-access span.emoji-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .quick-access h5 {
            font-weight: 700;
            margin-bottom: 8px;
            color: #ffb400; /* amarillo institucional */
        }

        .quick-access p {
            font-size: 0.9rem;
            color: #555;
            text-align: center;
        }

        .quick-access:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            background: #fff3c9;
            color: #000;
        }

        .quick-access p:hover {
            color: #333;
        }

        /* ===============================
           Bienvenida principal
        =============================== */
        .title-vet {
            background: linear-gradient(90deg, #fff3c9, #fff8e1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 40px 20px;
            border-radius: 15px;
        }

        .title-vet h2 {
            font-weight: 700;
            color: #ffb400;
        }

        .title-vet h2 span {
            color: #ffa500;
        }

        .title-vet p {
            font-size: 1.1rem;
            color: #555;
        }
    </style>
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
        <a href="index.php" class="active">Inicio</a>
        <a href="clientes.php">Clientes</a>
        <a href="mascotas.php">Mascotas</a>
        <a href="citas.php">Citas</a>
        <a href="reportes.php">Reportes</a>
        <a href="historias.php">Historias</a>
        <a href="consultorios.php">Consultorios</a>
        <a href="veterinarios.php">Veterinarios</a>
        <a href="formulas.php">Fórmulas</a>
    </nav>
</header>

<!-- ===============================
     CONTENIDO PRINCIPAL
================================ -->
<main class="flex-fill">
    <div class="container my-5">

        <!-- BIENVENIDA PRINCIPAL -->
        <div class="title-vet mb-5 text-center">
            <h2>🐾 Bienvenido a <span>Veterinaria Santiago Barrera</span></h2>
            <p>Accede a cada sección del sistema de manera rápida, sencilla y profesional</p>
        </div>

        <!-- TARJETAS DE ACCESO -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">

            <div class="col">
                <a href="clientes.php" class="quick-access h-100">
                    <span class="emoji-icon">👤</span>
                    <h5>Clientes</h5>
                    <p>Gestiona los propietarios de mascotas</p>
                </a>
            </div>

            <div class="col">
                <a href="mascotas.php" class="quick-access h-100">
                    <span class="emoji-icon">🐶</span>
                    <h5>Mascotas</h5>
                    <p>Registra y controla la información de las mascotas</p>
                </a>
            </div>

            <div class="col">
                <a href="citas.php" class="quick-access h-100">
                    <span class="emoji-icon">📅</span>
                    <h5>Citas</h5>
                    <p>Gestiona las citas de los clientes</p>
                </a>
            </div>

            <div class="col">
                <a href="reportes.php" class="quick-access h-100">
                    <span class="emoji-icon">📊</span>
                    <h5>Reportes</h5>
                    <p>Consulta reportes clínicos y administrativos</p>
                </a>
            </div>

            <div class="col">
                <a href="historias.php" class="quick-access h-100">
                    <span class="emoji-icon">📖</span>
                    <h5>Historias</h5>
                    <p>Consulta historias clínicas de mascotas</p>
                </a>
            </div>

            <div class="col">
                <a href="consultorios.php" class="quick-access h-100">
                    <span class="emoji-icon">🏥</span>
                    <h5>Consultorios</h5>
                    <p>Visualiza y gestiona los consultorios</p>
                </a>
            </div>

            <div class="col">
                <a href="veterinarios.php" class="quick-access h-100">
                    <span class="emoji-icon">👩‍⚕️</span>
                    <h5>Veterinarios</h5>
                    <p>Gestiona la información de los profesionales</p>
                </a>
            </div>

            <div class="col">
                <a href="formulas.php" class="quick-access h-100">
                    <span class="emoji-icon">💊</span>
                    <h5>Fórmulas</h5>
                    <p>Registra y consulta fórmulas médicas</p>
                </a>
            </div>

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
