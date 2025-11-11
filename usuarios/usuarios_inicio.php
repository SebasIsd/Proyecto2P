<?php
session_start();

// Verificar que haya una sesión activa y que sea docente o estudiante
if (!isset($_SESSION['correo']) || 
    !(strtolower($_SESSION['rol_nombre']) === 'docente' || strtolower($_SESSION['rol_nombre']) === 'estudiante')) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/conexion.php'; // Conexión a la BD

$cedula = $_SESSION['cedula'];

// Obtener datos reales de la BD
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM INSCRIPCIONES WHERE CED_USU = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$misEventos = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM INSCRIPCIONES WHERE CED_USU = ? AND ESTADO_INS = 'Inscrito'");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$inscripcionesAprobadas = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM INSCRIPCIONES WHERE CED_USU = ? AND ESTADO_INS = 'Preinscrito'");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$eventosPendientes = $stmt->get_result()->fetch_assoc()['total'];


// --- INICIO DEL CÓDIGO AÑADIDO ---
// RECORDATORIOS DE NOTA MÍNIMA PENDIENTE (Eventos que requieren una calificación para aprobar)
$stmt = $conn->prepare("
    SELECT DISTINCT 
        i.ID_INS,
        e.TIT_EVE_CUR,
        req.VALOR_MINIMO_APROBATORIO,
        r.NOM_REQ,
        e.FEC_FIN_EVE_CUR
    FROM 
        INSCRIPCIONES i
    INNER JOIN 
        EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
    INNER JOIN 
        EVENTOS_REQUISITOS req ON e.ID_EVE_CUR = req.ID_EVE_CUR /* Requisito de Nota */
    LEFT JOIN
        REQUISITOS r ON req.ID_REQ = r.ID_REQ
    WHERE 
        i.CED_USU = ? 
        AND i.ESTADO_INS IN ('Inscrito', 'Preinscrito') 
        AND req.VALOR_MINIMO_APROBATORIO IS NOT NULL /* Solo si requiere nota de aprobación */
        AND i.ESTADO_INS != 'Completado' /* Excluir eventos ya finalizados/aprobados */
");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$recordatoriosNota = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
// --- FIN DEL CÓDIGO AÑADIDO ---


// Participaciones por mes (últimos 6 meses)
$participacionesPorMes = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM INSCRIPCIONES i 
                           INNER JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
                           WHERE i.CED_USU = ? AND DATE_FORMAT(e.FEC_INI_EVE_CUR, '%Y-%m') = ?");
    $stmt->bind_param("ss", $cedula, $mes);
    $stmt->execute();
    $participacionesPorMes[] = $stmt->get_result()->fetch_assoc()['count'];
}
$mesesLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $mesesLabels[] = date('M', strtotime("-$i months"));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Usuario - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="usuarios_inicio.php" class="active"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="buscar_eventos.php"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <div class="content">
        <h1 class="mb-4">Dashboard Usuario</h1>
        <p>Bienvenido, <?= ucfirst($_SESSION['rol_nombre']) ?> (<?= $_SESSION['correo'] ?>)</p>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Mis Eventos</div>
                    <div class="card-body text-center">
                        <h2><?= $misEventos ?></h2>
                        <p>Eventos en los que participas.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Inscripciones Aprobadas</div>
                    <div class="card-body text-center">
                        <h2><?= $inscripcionesAprobadas ?></h2>
                        <p>Inscripciones confirmadas.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Eventos Pendientes</div>
                    <div class="card-body text-center">
                        <h2><?= $eventosPendientes ?></h2>
                        <p>Eventos por confirmar.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header-custom" style="background-color: #ffc107; color: #333;">
                        <i class="fas fa-exclamation-triangle"></i> Recordatorios de Aprobación (Nota Mínima)
                    </div>
                    <div class="card-body">
                        <?php if (count($recordatoriosNota) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recordatoriosNota as $recordatorio): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-certificate text-warning me-2"></i>
                                            Para el evento <strong><?= htmlspecialchars($recordatorio['TIT_EVE_CUR']) ?></strong>, debes obtener
                                            una calificación mínima de <strong><?= number_format($recordatorio['VALOR_MINIMO_APROBATORIO'], 2) ?></strong>
                                            en el requisito "<?= htmlspecialchars($recordatorio['NOM_REQ']) ?>".
                                        </div>
                                        <span class="badge bg-warning text-dark p-2">
                                            ¡Pendiente de Nota!
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-info text-center" role="alert">
                                <i class="fas fa-info-circle me-2"></i> Actualmente, no tienes eventos inscritos que requieran una nota de aprobación mínima.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Participaciones por Mes</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="participacionesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gráfico con Chart.js
        const ctx = document.getElementById('participacionesChart').getContext('2d');
        const participacionesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($mesesLabels) ?>,
                datasets: [{
                    label: 'Participaciones',
                    data: <?= json_encode($participacionesPorMes) ?>,
                    backgroundColor: 'rgba(163, 0, 0, 0.2)', /* Rojo con opacidad */
                    borderColor: 'rgba(163, 0, 0, 1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>