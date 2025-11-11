<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$cedula = $_SESSION['cedula'];

// Total eventos inscritos
$totalEventos = $conn->query("SELECT COUNT(*) as total FROM INSCRIPCIONES WHERE CED_USU = '$cedula'")->fetch_assoc()['total'];

// Eventos confirmados
$confirmados = $conn->query("SELECT COUNT(*) as total FROM INSCRIPCIONES WHERE CED_USU = '$cedula' AND ESTADO_INS = 'Confirmado'")->fetch_assoc()['total'];

// Horas totales (suma de horas de eventos confirmados)
$horas = $conn->query("
    SELECT SUM(e.HORAS_TOTALES) as total
    FROM INSCRIPCIONES i
    JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
    WHERE i.CED_USU = '$cedula' AND i.ESTADO_INS = 'Confirmado'
")->fetch_assoc()['total'] ?? 0;

// Participaciones por mes (últimos 6 meses)
$participaciones = [];
$labels = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i month"));
    $labels[] = date('M Y', strtotime($mes));
    $participaciones[] = $conn->query("
        SELECT COUNT(*) as count
        FROM INSCRIPCIONES i
        JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
        WHERE i.CED_USU = '$cedula' AND DATE_FORMAT(e.FEC_INI_EVE_CUR, '%Y-%m') = '$mes'
    ")->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Estadísticas - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Mismo estilo */
        .sidebar { /* igual */ }
        .content { margin-left: 260px; padding: 40px; }
        .stat-card { text-align: center; padding: 20px; border-radius: 16px; box-shadow: var(--shadow); background: white; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: var(--primary); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo"><img src="../images/favico.png" alt="Logo UTA"></div>
        <a href="usuarios_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="buscar_eventos.php"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="estadisticas.php" class="active"><i class="fas fa-chart-line"></i> <span>Mis Estadísticas</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <div class="content">
        <div class="page-header">
            <i class="fas fa-chart-line"></i>
            <h1>Mis Estadísticas</h1>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-calendar-check" style="font-size: 2rem; color: var(--primary);"></i>
                    <div class="stat-number"><?= $totalEventos ?></div>
                    <p>Total Eventos Inscritos</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--primary);"></i>
                    <div class="stat-number"><?= $confirmados ?></div>
                    <p>Eventos Confirmados</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-clock" style="font-size: 2rem; color: var(--primary);"></i>
                    <div class="stat-number"><?= $horas ?></div>
                    <p>Horas Totales</p>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header-custom">Participaciones por Mes</div>
            <div class="card-body">
                <canvas id="chartParticipaciones"></canvas>
            </div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('chartParticipaciones'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Participaciones',
                    data: <?= json_encode($participaciones) ?>,
                    borderColor: var(--primary),
                    backgroundColor: 'rgba(163,0,0,0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>