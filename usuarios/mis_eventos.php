<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$cedula = $_SESSION['cedula'];

// Cargar eventos inscritos con detalles
$eventos = $conn->query("
    SELECT 
        e.ID_EVE_CUR,
        e.TIT_EVE_CUR,
        e.FEC_INI_EVE_CUR,
        e.FEC_FIN_EVE_CUR,
        e.REQUIERE_ASISTENCIA,
        e.MOD_EVE_CUR,
        e.COS_EVE_CUR,
        t.NOM_TIPO_EVE,
        i.ESTADO_INS,
        i.EST_PAG_INS,
        i.ID_INS,  -- IMPORTANTE: agregar este campo
        p.URL_COMPROBANTE AS COMPROBANTE_PAGO,
        i.FEC_INI_INS,
        i.RUTA_CERTIFICADO
    FROM INSCRIPCIONES i
    JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    LEFT JOIN PAGOS p ON i.ID_INS = p.ID_INS
    WHERE i.CED_USU = '$cedula'
    ORDER BY i.FEC_INI_INS DESC
")->fetch_all(MYSQLI_ASSOC);
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
        --secondary: #2c3e50;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --gray-light: #f8f9fa;
        --gray: #6c757d;
        --dark: #333;
        --shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        --radius: 16px;
        --radius-sm: 12px;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
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
        border-left: 6px solid var(--primary);
    }

    .page-header i {
        font-size: 2rem;
        color: var(--primary);
        background: var(--primary-light);
        padding: 15px;
        border-radius: 50%;
    }

    .page-header h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--secondary);
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* EVENT ITEM STYLES */
    .event-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
    }

    .event-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        position: relative;
        height: fit-content;
    }

    .event-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary);
    }

    .event-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
        color: white;
        padding: 15px 20px;
        position: relative;
    }

    .event-card-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .event-type-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        backdrop-filter: blur(10px);
    }


    .event-card-body {
        padding: 20px;
    }

    .event-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        font-size: 0.9rem;
        color: var(--gray);
        flex-wrap: wrap;
    }

    .event-meta i {
        color: var(--primary);
        width: 14px;
        font-size: 0.8rem;
    }

    .event-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        background: var(--gray-light);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
    }

    .status-section {
        background: var(--gray-light);
        padding: 12px 15px;
        border-radius: var(--radius-sm);
        margin: 15px 0;
        border-left: 3px solid var(--primary);
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-preinscrito { 
        background: #fff3cd; 
        color: #856404; 
        border: 1px solid #ffeaa7;
    }
    .badge-confirmado { 
        background: #d4edda; 
        color: #155724; 
        border: 1px solid #c3e6cb;
    }
    .badge-cancelado { 
        background: #f8d7da; 
        color: #721c24; 
        border: 1px solid #f5c6cb;
    }
    .badge-asistio { 
        background: #d1ecf1; 
        color: #0c5460; 
        border: 1px solid #bee5eb;
    }
    .badge-completado { 
        background: #e2e3e5; 
        color: #383d41; 
        border: 1px solid #d6d8db;
    }

    .requisitos-section {
        margin: 15px 0;
    }

    .requisitos-section h6 {
        color: var(--secondary);
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.95rem;
    }

    .requisitos-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .requisitos-list li {
        background: var(--gray-light);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 5px;
        border: 1px solid #e9ecef;
    }


    .requisitos-list li:last-child {
        border-bottom: none;
    }

    .requisitos-list li i {
        color: var(--primary);
        font-size: 0.8rem;
    }

    .requisito-type {
        background: white;
        padding: 1px 6px;
        border-radius: 8px;
        font-size: 0.7rem;
        color: var(--gray);
        font-weight: 500;
    }


    .event-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary-custom {
        background: var(--primary);
        color: white;
    }

    .btn-primary-custom:hover {
        background: var(--primary-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(163,0,0,0.3);
    }

    .btn-success-custom {
        background: var(--success);
        color: white;
    }

    .btn-success-custom:hover {
        background: #218838;
        color: white;
        transform: translateY(-2px);
    }

    .btn-danger-custom {
        background: var(--danger);
        color: white;
    }

    .btn-danger-custom:hover {
        background: #c82333;
        color: white;
        transform: translateY(-2px);
    }

    .btn-outline-custom {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }

    .btn-secondary-custom {
        background: var(--gray);
        color: white;
    }

    .btn-secondary-custom:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-2px);
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    /* MODAL STYLES */
    .modal-content {
        border-radius: var(--radius);
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
        color: white;
        border-radius: var(--radius) var(--radius) 0 0;
        padding: 20px 25px;
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 30px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .document-section {
        margin-bottom: 25px;
        padding: 20px;
        border-radius: var(--radius-sm);
        background: var(--gray-light);
        border-left: 4px solid var(--primary);
    }

    .document-section h6 {
        color: var(--secondary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .document-section img {
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .document-section embed {
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    /* EMPTY STATE */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: var(--gray);
        margin-bottom: 10px;
        font-weight: 300;
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
        .event-grid {
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        }
    }

    @media (max-width: 992px) {
        .event-grid {
            grid-template-columns: 1fr;
        }
        
        .content {
            margin-left: 0;
            padding: 20px;
        }
        
        .sidebar {
            width: 80px;
        }
        
        .sidebar .logo img {
            width: 50px;
        }
        
        .sidebar a span {
            display: none;
        }
        
        .sidebar a {
            padding: 16px;
            justify-content: center;
        }
        
        .sidebar a:hover {
            padding-left: 16px;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .event-actions {
            flex-direction: column;
        }
        
        .btn-action {
            justify-content: center;
        }
    }

    /* ANIMATIONS */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .event-card {
        animation: fadeIn 0.6s ease-out;
    }

    /* SCROLLBAR STYLING */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: var(--primary-hover);
    }
    /* SEARCH BAR STYLES */
    .search-container {
        background: white;
        padding: 20px;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }

    .search-box {
        position: relative;
        max-width: 500px;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    .search-box input {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s;
        background: var(--gray-light);
    }

    .search-box input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(163, 0, 0, 0.1);
        background: white;
    }

    .search-results-info {
        margin-top: 10px;
        color: var(--gray);
        font-size: 0.9rem;
    }

    .no-results {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray);
    }

    .no-results i {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 15px;
    }
    .btn-info-custom {
        background: #17a2b8;
        color: white;
    }
    .btn-info-custom:hover {
        background: #138496;
        color: white;
        transform: translateY(-2px);
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

    <div class="content">
        <div class="page-header">
            <h2 class="text-dark"><i class="fas fa-calendar-check"></i> Mis Eventos</h2>
        </div>
            <!-- Search Bar -->
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar eventos por nombre..." onkeyup="filtrarEventos()">
            </div>
            <div class="search-results-info">
                <span id="resultsCount"><?= count($eventos) ?> eventos encontrados</span>
            </div>
        </div>
        <?php if (empty($eventos)): ?>
            <div class="text-center py-5 bg-white rounded shadow">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No tienes eventos inscritos</h4>
                    <p class="text-muted">Busca eventos disponibles y regístrate.</p>
                    <a href="buscar_eventos.php" class="btn btn-primary">Buscar Eventos</a>
                </div>
        <?php else: ?>
            <?php foreach ($eventos as $e): ?>
                <?php 
                // Cargar requisitos del evento
                $reqs = $conn->query("
                    SELECT r.NOM_REQ, r.TIPO 
                    FROM EVENTOS_REQUISITOS er
                    JOIN REQUISITOS r ON er.ID_REQ = r.ID_REQ
                    WHERE er.ID_EVE_CUR = {$e['ID_EVE_CUR']}
                ")->fetch_all(MYSQLI_ASSOC);

                // Cargar evidencias subidas por el usuario - CONSULTA CORREGIDA
                $evidencias = $conn->query("
                    SELECT r.ID_REQ, r.NOM_REQ, r.TIPO, e.VALOR_TEXTO, e.VALOR_NUMERICO, e.NOMBRE_ARCHIVO, e.URL_ARCHIVO, e.TIPO_MIME
                    FROM EVIDENCIAS e
                    JOIN REQUISITOS r ON e.ID_REQ = r.ID_REQ
                    WHERE e.ID_INS = {$e['ID_INS']}
                ")->fetch_all(MYSQLI_ASSOC);
                ?>

                <div class="event-card">
                    <div class="event-card-header">
                        <h5><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></h5>
                        <span class="event-type-badge"><?= htmlspecialchars($e['NOM_TIPO_EVE']) ?></span>
                    </div>
                    
                    <div class="event-card-body">
                        <!-- Información del evento -->
                        <div class="event-meta">
                            <i class="fas fa-calendar"></i>
                            <span>
                                <?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?>
                                <?php if ($e['FEC_FIN_EVE_CUR'] && $e['FEC_FIN_EVE_CUR'] !== $e['FEC_INI_EVE_CUR']): ?>
                                    al <?= date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR'])) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="event-meta">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>
                                <strong><?= $e['MOD_EVE_CUR'] == 'Pagado' 
                                    ? 'Pagado ($' . number_format($e['COS_EVE_CUR'], 2) . ')' 
                                    : 'Gratis' ?></strong>
                            </span>
                        </div>

                        <!-- Estado -->
                        <div class="status-section">
                            <strong>Estado de Inscripción:</strong>
                            <div class="mt-2">
                                <span class="status-badge badge-<?= strtolower($e['ESTADO_INS']) ?>">
                                    <i class="fas fa-<?= 
                                        $e['ESTADO_INS'] == 'Preinscrito' ? 'clock' : 
                                        ($e['ESTADO_INS'] == 'Confirmado' ? 'check-circle' : 
                                        ($e['ESTADO_INS'] == 'Asistió' ? 'user-check' : 
                                        ($e['ESTADO_INS'] == 'Cancelado' ? 'times-circle' : 'flag'))) 
                                    ?>"></i>
                                    <?= ucfirst($e['ESTADO_INS']) ?>
                                </span>
                                <?php if ($e['ESTADO_INS'] == 'Preinscrito'): ?>
                                    <p class="mt-2 mb-0 text-muted small">
                                        <i class="fas fa-info-circle"></i>
                                        Esperando aprobación 
                                        <?= $e['MOD_EVE_CUR'] == 'Pagado' ? 'y verificación de pago' : 'del docente/admin' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Requisitos -->
                        <div class="requisitos-section">
                            <h6><i class="fas fa-clipboard-list"></i> Requisitos del Evento</h6>
                            <?php if (empty($reqs)): ?>
                                <p class="text-muted mb-0"><i class="fas fa-check-circle text-success"></i> No requiere requisitos adicionales.</p>
                            <?php else: ?>
                                <ul class="requisitos-list">
                                    <?php foreach ($reqs as $r): ?>
                                        <li>
                                            <i class="fas fa-<?= 
                                                $r['TIPO'] == 'DOCUMENTO' ? 'file-pdf' : 
                                                ($r['TIPO'] == 'TEXTO_CORTO' ? 'font' : 
                                                ($r['TIPO'] == 'NUMERICO' ? 'calculator' : 'file-alt')) 
                                            ?>"></i>
                                            <?= htmlspecialchars($r['NOM_REQ']) ?>
                                            <!--<span class="requisito-type"><?= $r['TIPO'] ?></span>-->
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                            <?php
                            // Verificar si todos los requisitos están aprobados
                            $req_aprobados = true;
                            $evidencias = $conn->query("SELECT * FROM EVIDENCIAS WHERE ID_INS = {$e['ID_INS']}")->fetch_all(MYSQLI_ASSOC);
                            foreach ($evidencias as $ev) {
                                if ($ev['ESTADO_VALIDACION'] !== 'Aprobado') {
                                    $req_aprobados = false;
                                    break;
                                }
                            }

                        // Si es pagado, requisitos aprobados, y pago pendiente
                        if ($e['MOD_EVE_CUR'] == 'Pagado' && $req_aprobados && $e['EST_PAG_INS'] == 'Pendiente' && empty($e['COMPROBANTE_PAGO'])): ?>
                            <div class="alert alert-success mt-3">
                                <i class="fas fa-check"></i> Tus requisitos han sido aprobados. 
                                Ahora sube el comprobante de pago para completar tu inscripción.
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data" action="procesar_pago.php">
                                <input type="hidden" name="id_ins" value="<?= $e['ID_INS'] ?>">
                                <div class="mb-3">
                                    <label class="form-label text-danger">Comprobante de Pago * (OBLIGATORIO)</label>
                                    <input type="file" class="form-control" name="comprobante_pago" required accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Máx. 5MB. Formatos: PDF, JPG, PNG</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Subir Comprobante
                                </button>
                            </form>
                        <?php elseif ($e['MOD_EVE_CUR'] == 'Pagado' && $e['EST_PAG_INS'] == 'Pendiente' && !empty($e['COMPROBANTE_PAGO'])): ?>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-clock"></i> Tu comprobante de pago ha sido subido y está pendiente de revisión por el administrador.
                            </div>
                        <?php endif; ?>

                        <!-- Botón Ver Documentos -->
                        <?php if ($e['COMPROBANTE_PAGO'] || !empty($evidencias)): ?>
                            <div class="mt-3">
                            <button class="btn-action btn-outline-custom" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#documentosModal"
                                    onclick="cargarDocumentos(<?= $e['ID_EVE_CUR'] ?>, '<?= addslashes($e['TIT_EVE_CUR']) ?>', '<?= $e['COMPROBANTE_PAGO'] ?>', <?= htmlspecialchars(json_encode($evidencias), ENT_QUOTES, 'UTF-8') ?>)">
                                <i class="fas fa-folder-open"></i> Ver Documentos Subidos
                            </button>
                            </div>
                        <?php endif; ?>

                        <!-- Acciones -->
                        <div class="event-actions">
                            <a href="detalle_evento.php?id=<?= $e['ID_EVE_CUR'] ?>" class="btn-action btn-primary-custom">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </a>

                            <?php if ($e['ESTADO_INS'] == 'Completado' && !empty($e['RUTA_CERTIFICADO'])): ?>
                                <button type="button"
                                   class="btn-action btn-info-custom" 
                                   data-bs-toggle="modal" 
                                   data-bs-target="#certificadoModal"
                                   data-ruta-certificado="../<?= htmlspecialchars($e['RUTA_CERTIFICADO']) ?>"
                                   data-titulo-evento="<?= htmlspecialchars($e['TIT_EVE_CUR']) ?>">
                                    <i class="fas fa-award"></i> Ver Certificado
                                </button>
                            <?php endif; ?>

                            <?php if ($e['REQUIERE_ASISTENCIA'] && $e['ESTADO_INS'] == 'Confirmado'
                                && strtotime($e['FEC_INI_EVE_CUR']) <= time()
                                && strtotime($e['FEC_FIN_EVE_CUR']) >= time()): ?>
                                <button class="btn-action btn-success-custom" onclick="registrarAsistencia(<?= $e['ID_EVE_CUR'] ?>)">
                                    <i class="fas fa-check"></i> Registrar Asistencia
                                </button>
                            <?php endif; ?>

                            <?php 
                            // Solo mostramos el botón si está Preinscrito, Inscrito o Confirmado
                            if (in_array($e['ESTADO_INS'], ['Preinscrito', 'Inscrito', 'Confirmado'])): 
                                
                                // Lo deshabilitamos si es 'Inscrito' o 'Confirmado' (según tu solicitud)
                                $isDisabled = in_array($e['ESTADO_INS'], ['Inscrito', 'Confirmado']);
                            ?>
                                <button class="btn-action btn-danger-custom" 
                                        onclick="cancelarInscripcion(<?= $e['ID_EVE_CUR'] ?>)"
                                        <?php if ($isDisabled): ?>
                                            disabled title="No se puede cancelar una inscripción ya confirmada/inscrita."
                                        <?php endif; ?>>
                                    <i class="fas fa-times"></i> Cancelar Inscripción
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- MODAL DE DOCUMENTOS -->
    <div class="modal fade" id="documentosModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTituloDocs">Documentos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContenidoDocs">
                    <!-- Se llena con JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="certificadoModal" tabindex="-1" aria-labelledby="certificadoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="certificadoModalLabel">Certificado de Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 0; height: 80vh;">
                    <iframe id="certificadoFrame" src="" width="100%" height="100%" frameborder="0">
                        Tu navegador no soporta iframes.
                    </iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function registrarAsistencia(id) {
            if (!confirm('¿Registrar asistencia?')) return;
            fetch('registrar_asistencia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_evento=' + id
            })
            .then(r => r.json())
            .then(d => { alert(d.message); if (d.success) location.reload(); })
            .catch(() => alert('Error de conexión'));
        }

        function cancelarInscripcion(id) {
            if (!confirm('¿Cancelar inscripción?')) return;
            fetch('cancelar_inscripcion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_evento=' + id
            })
            .then(r => r.json())
            .then(d => { alert(d.message); if (d.success) location.reload(); })
            .catch(() => alert('Error de conexión'));
        }

function cargarDocumentos(id_evento, titulo, comprobante, evidencias) {
            console.log("Comprobante:", comprobante);
            console.log("Evidencias:", evidencias);
            
            const modalTitulo = document.getElementById('modalTituloDocs');
            const modalBody = document.getElementById('modalContenidoDocs');
            
            modalTitulo.textContent = `Documentos - ${titulo}`;
            let html = '';

            // COMPROBANTE
            if (comprobante && comprobante !== 'null' && comprobante !== '') {
                const path = '../uploads/comprobantes/' + comprobante;
                const ext = comprobante.split('.').pop().toLowerCase();
                html += `<div class="mb-4 p-3 border rounded bg-light">
                    <h6 class="text-primary"><i class="fas fa-file-invoice-dollar"></i> Comprobante de Pago</h6>`;
                
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    html += `<img src="${path}" class="img-fluid" style="max-height: 500px;" alt="Comprobante de pago">`;
                } else if (ext === 'pdf') {
                    html += `<embed src="${path}" type="application/pdf" width="100%" height="600px" />`;
                }
                html += `</div>`;
            }

            // REQUISITOS
            if (evidencias && evidencias.length > 0) {
                html += `<h6 class="text-success mb-3"><i class="fas fa-clipboard-list"></i> Requisitos Entregados</h6>`;
                evidencias.forEach(ev => {
                    html += `<div class="mb-3 p-3 border rounded">
                        <strong>${ev.NOM_REQ} (${ev.TIPO})</strong><br>`;
                    if (ev.TIPO === 'TEXTO_CORTO' && ev.VALOR_TEXTO) {
                        html += `<p class="mb-0 mt-2"><strong>Texto:</strong> "${ev.VALOR_TEXTO}"</p>`;
                    } else if (ev.TIPO === 'NUMERICO') {
                        if (ev.VALOR_NUMERICO) {
                            html += `<p class="mb-0 mt-2"><strong>Nota:</strong> ${ev.VALOR_NUMERICO}</p>`;
                        } else {
                            html += `<p class="mb-0 mt-2 text-muted"><i>Pendiente de evaluación</i></p>`;
                        }
                    } else if (ev.NOMBRE_ARCHIVO) {
                        const path = '../uploads/requisitos/' + ev.NOMBRE_ARCHIVO;
                        const ext = ev.NOMBRE_ARCHIVO.split('.').pop().toLowerCase();
                        if (['jpg', 'jpeg', 'png'].includes(ext)) {
                            html += `<img src="${path}" class="img-fluid mt-2" style="max-height: 400px;" alt="${ev.NOM_REQ}">`;
                        } else if (ext === 'pdf') {
                            html += `<embed src="${path}" type="application/pdf" width="100%" height="500px" class="border rounded mt-2" />`;
                        }
                    } else {
                        html += `<span class="text-muted">No entregado</span>`;
                    }
                    html += `</div>`;
                });
            } else {
                html += `<p class="text-muted">No se han subido requisitos.</p>`;
            }

            modalBody.innerHTML = html;
        }
        // Función para filtrar eventos en tiempo real
        function filtrarEventos() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const eventCards = document.querySelectorAll('.event-card');
            let visibleCount = 0;
            
            eventCards.forEach(card => {
                const eventTitle = card.querySelector('.event-card-header h5').textContent.toLowerCase();
                const eventType = card.querySelector('.event-type-badge').textContent.toLowerCase();
                
                if (eventTitle.includes(searchTerm) || eventType.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Actualizar contador de resultados
            document.getElementById('resultsCount').textContent = `${visibleCount} eventos encontrados`;
            
            // Mostrar mensaje si no hay resultados
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResults) {
                    const eventGrid = document.querySelector('.event-grid');
                    const noResultsHTML = `
                        <div class="no-results" id="noResults">
                            <i class="fas fa-search"></i>
                            <h4>No se encontraron eventos</h4>
                            <p>No hay eventos que coincidan con "<strong>${searchTerm}</strong>"</p>
                        </div>
                    `;
                    eventGrid.insertAdjacentHTML('afterend', noResultsHTML);
                }
            } else if (noResults) {
                noResults.remove();
            }
        }

        const certificadoModal = document.getElementById('certificadoModal');
        if (certificadoModal) {
            certificadoModal.addEventListener('show.bs.modal', function (event) {
                // Obtener el botón que disparó el modal
                const button = event.relatedTarget;
                
                // Extraer la información de los atributos data-*
                const rutaCertificado = button.getAttribute('data-ruta-certificado');
                const tituloEvento = button.getAttribute('data-titulo-evento');

                // Actualizar el contenido del modal
                const modalTitle = certificadoModal.querySelector('.modal-title');
                const modalIframe = certificadoModal.querySelector('#certificadoFrame');

                modalTitle.textContent = 'Certificado: ' + tituloEvento;
                modalIframe.setAttribute('src', rutaCertificado);
            });

            // Opcional: Limpiar el iframe cuando se cierra el modal
            // Esto detiene la carga del PDF y libera memoria
            certificadoModal.addEventListener('hide.bs.modal', function (event) {
                const modalIframe = certificadoModal.querySelector('#certificadoFrame');
                modalIframe.setAttribute('src', '');
            });
        }

        // También agrega esto para el empty state cuando no hay eventos
        /*<?php if (empty($eventos)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('searchInput').style.display = 'none';
                document.querySelector('.search-results-info').style.display = 'none';
            });
        <?php endif; ?>*/
    </script>
</body>
</html>