<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['correo']) || !isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$cedula = $_SESSION['cedula'];

// Cargar eventos inscritos utilizando una SENTENCIA PREPARADA
$sql = "
    SELECT 
        e.ID_EVE_CUR,
        e.TIT_EVE_CUR,
        e.FEC_INI_EVE_CUR,
        e.FEC_FIN_EVE_CUR,
        t.NOM_TIPO_EVE,
        i.ESTADO_INS,
        i.FEC_INI_INS
    FROM INSCRIPCIONES i
    JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    WHERE i.CED_USU = ?
    ORDER BY i.FEC_INI_INS DESC
";

// 1. Preparar la consulta
$stmt = $conn->prepare($sql);

// 2. Vincular el parámetro (s = string)
if ($stmt) {
    $stmt->bind_param("s", $cedula);

    // 3. Ejecutar la consulta
    $stmt->execute();

    // 4. Obtener el resultado
    $result = $stmt->get_result();

    // 5. Cargar eventos inscritos
    $eventos = $result->fetch_all(MYSQLI_ASSOC);
    
    // 6. Cerrar el statement
    $stmt->close();
} else {
    // Manejo de error si la preparación falla
    die("Error en la preparación de la consulta: " . $conn->error);
}

// Nota: La conexión ($conn) se cierra generalmente al final del script o en 'conexion.php' si es una función de cierre.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Eventos - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .badge-preinscrito { background: #fff3cd; color: #856404; }
        .badge-confirmado { background: #d4edda; color: #155724; }
        .badge-cancelado { background: #f8d7da; color: #721c24; }
        .badge-asistio { background: #d1ecf1; color: #0c5460; }

        .btn-ver {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-ver:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-cancelar {
            background: transparent;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .btn-cancelar:hover {
            background: #dc3545;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo img { width: 50px; }
            .sidebar a span { display: none; }
            .sidebar a { padding: 16px; justify-content: center; }
            .sidebar a:hover { padding-left: 16px; }
            .content { margin-left: 80px; padding: 20px; }
            .event-item { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
        .event-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px dashed #eee;
            margin: 0 10px;
        }

        .event-item:last-child {
            border-bottom: none;
        }

        .event-info h5 {
            margin: 0 0 6px;
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 600;
        }

        .event-info small {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .event-badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="usuarios_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php" class="active"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="buscar_eventos.php"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <!-- Contenido -->
    <div class="content">
        <div class="page-header">
            <i class="fas fa-calendar-alt"></i>
            <h1>Mis Eventos Inscritos</h1>
        </div>

        <div class="card">
            <div class="card-header-custom">
                <i class="fas fa-list me-2"></i> Eventos en los que estás inscrito
            </div>
            <div class="card-body-custom">
                <?php if (empty($eventos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h4>No estás inscrito en ningún evento</h4>
                        <p>Explora los eventos disponibles y regístrate en los que te interesen.</p>
                        <a href="buscar_eventos.php" class="btn-ver mt-3">
                            <i class="fas fa-search"></i> Buscar Eventos
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($eventos as $e): ?>
                        <div class="event-item">
                            <div class="event-info">
                                <h5><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></h5>
                                <small>
                                    <i class="fas fa-calendar"></i> 
                                    <?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?>
                                    <?php if ($e['FEC_FIN_EVE_CUR'] && $e['FEC_FIN_EVE_CUR'] !== $e['FEC_INI_EVE_CUR']): ?>
                                        al <?= date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR'])) ?>
                                    <?php endif; ?>
                                    • <i class="fas fa-tag"></i> <?= htmlspecialchars($e['NOM_TIPO_EVE']) ?>
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="event-badge badge-<?= strtolower(str_replace(' ', '', $e['ESTADO_INS'])) ?>">
                                    <?= ucfirst($e['ESTADO_INS']) ?>
                                </span>
                                <a href="detalle_evento.php?id=<?= $e['ID_EVE_CUR'] ?>" class="btn-ver">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <?php if (in_array($e['ESTADO_INS'], ['Preinscrito', 'Confirmado'])): ?>
                                    <button class="btn-cancelar" onclick="cancelarInscripcion(<?= $e['ID_EVE_CUR'] ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelarInscripcion(idEvento) {
            if (confirm('¿Estás seguro de cancelar tu inscripción a este evento?')) {
                fetch('cancelar_inscripcion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id_evento=' + idEvento
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => alert('Error al cancelar'));
            }
        }
    </script>
</body>
</html>