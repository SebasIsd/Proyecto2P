<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$id = $_GET['id'] ?? 0;
$cedula = $_SESSION['cedula'];

$stmt = $conn->prepare("
    SELECT e.*, t.NOM_TIPO_EVE,
           (SELECT COUNT(*) FROM INSCRIPCIONES WHERE ID_EVE_CUR = e.ID_EVE_CUR AND CED_USU = ?) AS inscrito
    FROM EVENTOS_CURSOS e
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    WHERE e.ID_EVE_CUR = ? AND e.ACTIVO = 1
");
$stmt->bind_param("si", $cedula, $id);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if (!$evento) {
    header("Location: buscar_eventos.php");
    exit();
}

// Requisitos
$requisitos = $conn->query("
    SELECT r.ID_REQ, r.NOM_REQ, r.TIPO
    FROM EVENTOS_REQUISITOS er
    JOIN REQUISITOS r ON er.ID_REQ = r.ID_REQ
    WHERE er.ID_EVE_CUR = $id
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($evento['TIT_EVE_CUR']) ?> - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mismo CSS que antes */
        :root {
            --primary: #a30000;
            --primary-hover: #d51313;
            --primary-light: #ffebeb;
            --gray-light: #f8f9fa;
            --gray: #6c757d;
            --dark: #333;
            --shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            --radius: 16px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            color: var(--dark);
            min-height: 100vh;
        }

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
            margin-bottom: 40px;
            padding: 0 25px;
        }

        .sidebar .logo img {
            width: 130px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.25);
            transition: all 0.3s;
        }

        .sidebar .logo img:hover {
            transform: scale(1.08);
            border-color: white;
        }

        .sidebar a {
            color: rgba(255,255,255,0.9);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
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
            font-size: 1.15rem;
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: var(--primary);
            color: white;
        }

        .table thead th {
            border: none;
            font-weight: 600;
            padding: 16px;
            font-size: 0.95rem;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-color: #eee;
        }

        .table tbody tr:hover {
            background: var(--primary-light);
        }

        .badge {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-edit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(163,0,0,0.2);
        }

        .avatar-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Modal */
        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: var(--primary);
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
            padding: 18px 25px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 30px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(163,0,0,0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .btn-save-modal {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-save-modal:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo img { width: 50px; }
            .sidebar a span { display: none; }
            .sidebar a { padding: 16px; justify-content: center; }
            .sidebar a:hover { padding-left: 16px; }
            .content { margin-left: 80px; padding: 20px; }
        }
        .content { margin-left: 260px; padding: 40px; }
        .page-header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 25px; margin-bottom: 30px; }
        .card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); }
        .btn-inscribir { background: var(--primary); color: white; padding: 12px 30px; border-radius: 12px; font-weight: 600; 
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; }
        .btn-inscribir:hover { background: var(--primary-hover); }
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
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <div class="content">
        <div class="page-header">
            <i class="fas fa-info-circle"></i>
            <h1>Detalle del Evento</h1>
        </div>

        <div class="card">
            <div class="card-header-custom"><?= htmlspecialchars($evento['NOM_TIPO_EVE']) ?></div>
            <div class="card-body p-4">
                <h3><?= htmlspecialchars($evento['TIT_EVE_CUR']) ?></h3>
                <p class="text-muted"><?= nl2br(htmlspecialchars($evento['DES_EVE_CUR'])) ?></p>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($evento['FEC_INI_EVE_CUR'])) ?> 
                            <?= $evento['FEC_FIN_EVE_CUR'] ? ' al ' . date('d/m/Y', strtotime($evento['FEC_FIN_EVE_CUR'])) : '' ?>
                        </p>
                        <p><strong>Lugar:</strong> <?= htmlspecialchars($evento['LUGAR']) ?></p>
                        <p><strong>Cupos:</strong> <?= $evento['CUPOS_DISPONIBLES'] ?> / <?= $evento['CAPACIDAD_MAXIMA'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Modalidad:</strong> <?= $evento['MOD_EVE_CUR'] == 'Pagado' ? 'Pagado ($' . $evento['COS_EVE_CUR'] . ')' : 'Gratis' ?></p>
                        <p><strong>Horas:</strong> <?= $evento['HORAS_TOTALES'] ?? 'No especificado' ?></p>
                    </div>
                </div>

                <?php if (!empty($requisitos)): ?>
                    <hr>
                    <h5>Requisitos para inscribirse:</h5>
                    <ul>
                        <?php foreach ($requisitos as $r): ?>
                            <li><?= htmlspecialchars($r['NOM_REQ']) ?> (<?= $r['TIPO'] ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="mt-4">
                    <?php if ($evento['inscrito']): ?>
                        <button class="btn btn-success" disabled>
                            <i class="fas fa-check"></i> Ya estás inscrito
                        </button>
                    <?php elseif ($evento['CUPOS_DISPONIBLES'] > 0): ?>
                        <a href="inscribirme.php?id=<?= $id ?>" class="btn-inscribir">
                            <i class="fas fa-user-plus"></i> Inscribirme
                        </a>
                    <?php else: ?>
                        <button class="btn btn-danger" disabled>Cupos agotados</button>
                    <?php endif; ?>
                    <a href="buscar_eventos.php" class="btn btn-secondary ms-2">Volver</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>