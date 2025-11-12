<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/conexion.php';

$cedula = $_SESSION['cedula'];
$stmt = $conn->prepare("SELECT CED_USU, NOM_PRI_USU, NOM_SEG_USU, APE_PRI_USU, APE_SEG_USU, COR_USU, TEL_USU, DIR_USU FROM USUARIOS WHERE CED_USU = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
<style>
    :root {
        --primary: #a30000;
        --primary-hover: #d51313;
        --primary-light: #fff4f4;
        --gray-light: #f8f9fa;
        --gray: #6c757d;
        --dark: #2b2b2b;
        --shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        --radius: 16px;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #fafafa 0%, #eaeaea 100%);
        color: var(--dark);
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
        position: fixed;
        top: 0; left: 0;
        width: 260px;
        height: 100vh;
        background: var(--primary);
        color: white;
        padding: 25px 0;
        box-shadow: 5px 0 20px rgba(0,0,0,0.15);
        z-index: 1000;
    }

    .sidebar .logo {
        text-align: center;
        margin-bottom: 35px;
        padding: 0 20px;
    }

    .sidebar .logo img {
        width: 120px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.25);
        transition: all 0.3s;
    }

    .sidebar .logo img:hover {
        transform: scale(1.08);
        border-color: #fff;
    }

    .sidebar a {
        color: rgba(255,255,255,0.9);
        padding: 14px 28px;
        display: flex;
        align-items: center;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.3s;
        border-left: 4px solid transparent;
    }

    .sidebar a i {
        width: 24px;
        margin-right: 14px;
        font-size: 1.15rem;
    }

    .sidebar a:hover, .sidebar a.active {
        background: var(--primary-hover);
        color: white;
        border-left-color: white;
        padding-left: 32px;
    }

    /* ===== CONTENT ===== */
    .content {
        margin-left: 260px;
        padding: 40px;
    }

    .page-header {
        background: white;
        padding: 25px 30px;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-header i {
        font-size: 1.8rem;
        color: var(--primary);
    }

    .page-header h1 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--dark);
    }

    /* ===== PROFILE CARD ===== */
    .card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .card-header-custom {
        background: var(--primary);
        color: white;
        padding: 18px 28px;
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-body-custom {
        padding: 30px;
    }

    /* ===== AVATAR & HEADER ===== */
    .avatar-circle {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: var(--primary-light);
        color: var(--primary);
        font-weight: bold;
        font-size: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(163,0,0,0.2);
    }

    .profile-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .profile-header h3 {
        margin: 10px 0 6px;
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--dark);
    }

    .role-badge {
        display: inline-block;
        background: var(--primary);
        color: white;
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== INFO GRID ===== */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 15px 25px;
        margin-top: 10px;
    }

    .info-item {
        background: var(--gray-light);
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s;
    }

    .info-item:hover {
        background: #f1f1f1;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    .info-item i {
        color: var(--primary);
        font-size: 1.2rem;
        width: 28px;
        text-align: center;
    }

    .info-item span {
        color: var(--dark);
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .info-item strong {
        color: var(--gray);
        display: block;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 3px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .sidebar { width: 80px; }
        .sidebar .logo img { width: 50px; }
        .sidebar a span { display: none; }
        .sidebar a { padding: 16px; justify-content: center; }
        .sidebar a:hover { padding-left: 16px; }
        .content { margin-left: 80px; padding: 20px; }
    }
    /* === PROGRESO DEL USUARIO === */
    .progress-container {
        background: #f0f0f0;
        border-radius: 50px;
        height: 25px;
        width: 100%;
        overflow: hidden;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
    }

    .progress-bar-custom {
        height: 100%;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-hover) 100%);
        color: white;
        font-weight: 600;
        text-align: center;
        line-height: 25px;
        border-radius: 50px;
        transition: width 1s ease-in-out;
        box-shadow: 0 3px 10px rgba(163, 0, 0, 0.3);
    }
</style>

</head>
<body>

    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="usuarios_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="buscar_eventos.php"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php" class="active"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <div class="content">
        <div class="page-header">
            <i class="fas fa-user"></i>
            <h1>Mi Perfil</h1>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($usuario['NOM_PRI_USU'], 0, 1) . substr($usuario['APE_PRI_USU'], 0, 1)) ?>
                            </div>
                            <div class="profile-header">
                                <h3><?= htmlspecialchars(trim($usuario['NOM_PRI_USU'] . ' ' . ($usuario['NOM_SEG_USU'] ?? '') . ' ' . $usuario['APE_PRI_USU'] . ' ' . ($usuario['APE_SEG_USU'] ?? ''))) ?></h3>
                                <div class="role-badge"><?= ucfirst($_SESSION['rol_nombre']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header-custom">
                            <i class="fas fa-id-card me-2"></i> Información General
                        </div>
                        <div class="card-body-custom">
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fas fa-id-badge"></i>
                                    <span><strong>Cédula</strong><?= $usuario['CED_USU'] ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><strong>Correo</strong><?= $usuario['COR_USU'] ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <span><strong>Teléfono</strong><?= $usuario['TEL_USU'] ?? 'No registrado' ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Dirección</strong><?= $usuario['DIR_USU'] ?? 'No registrada' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header-custom">
                            <i class="fas fa-info-circle me-2"></i> Detalles del Usuario
                        </div>
                        <div class="card-body-custom">
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span><strong>Rol</strong><?= ucfirst($_SESSION['rol_nombre']) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <span><strong>Eventos registrados</strong> Puedes consultarlos en <a href="mis_eventos.php">“Mis Eventos”</a>.</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span><strong>Estado de cuenta</strong> Activo y verificado</span>
                                </div>
                            </div>
                            <hr class="my-4">
                            <p class="text-muted text-center">
                                Si deseas actualizar tus datos personales o restablecer tu contraseña,
                                comunícate con el área de soporte institucional.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Progreso del Usuario -->
                    <div class="card mt-4">
                        <div class="card-header-custom">
                            <i class="fas fa-chart-line me-2"></i> Progreso del Usuario
                        </div>
                        <div class="card-body-custom text-center">
                            <?php
                            include '../includes/conexion.php';
                            $cedula = $_SESSION['cedula'];

                            // Total de eventos activos
                            $queryTotal = "SELECT COUNT(*) AS total FROM eventos_cursos WHERE ACTIVO = 1";
                            $totalEventos = $conn->query($queryTotal)->fetch_assoc()['total'] ?? 0;

                            // Eventos en los que el usuario está inscrito
                            $queryInscritos = "SELECT COUNT(*) AS inscritos FROM inscripciones WHERE CED_USU = '$cedula'";

                            $inscritos = $conn->query($queryInscritos)->fetch_assoc()['inscritos'] ?? 0;

                            $porcentaje = ($totalEventos > 0) ? round(($inscritos / $totalEventos) * 100, 1) : 0;
                            ?>

                            <h5 class="mb-3">Tu participación general</h5>

                            <div class="progress-container">
                                <div class="progress-bar-custom" style="width: <?= $porcentaje ?>%;">
                                    <?= $porcentaje ?>%
                                </div>
                            </div>

                            <p class="mt-3 text-muted">
                                Has participado en <strong><?= $inscritos ?></strong> de <strong><?= $totalEventos ?></strong> eventos disponibles.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>