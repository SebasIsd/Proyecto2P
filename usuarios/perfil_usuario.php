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

$stmt_cert = $conn->prepare("
    SELECT 
        e.TIT_EVE_CUR,
        e.HORAS_TOTALES,
        e.FEC_INI_EVE_CUR,
        e.FEC_FIN_EVE_CUR,
        i.ESTADO_INS,
        i.RUTA_CERTIFICADO
    FROM INSCRIPCIONES i
    JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
    WHERE i.CED_USU = ? AND i.ESTADO_INS = 'Completado' AND i.RUTA_CERTIFICADO IS NOT NULL
    ORDER BY e.FEC_FIN_EVE_CUR DESC
");
$stmt_cert->bind_param("s", $cedula);
$stmt_cert->execute();
$certificados = $stmt_cert->get_result()->fetch_all(MYSQLI_ASSOC);
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
                    
                    <div class="card mt-4">
                        <div class="card-header-custom">
                            <i class="fas fa-award me-2"></i> 
                            Certificados Otorgados a: <?= htmlspecialchars(trim($usuario['NOM_PRI_USU'] . ' ' . $usuario['APE_PRI_USU'])) ?>
                        </div>
                        <div class="card-body-custom">
                            <?php if (empty($certificados)): ?>
                                <p class="text-muted text-center mb-0">
                                    <i class="fas fa-info-circle"></i> Aún no tienes certificados.
                                </p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($certificados as $cert): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                            <div>
                                                <h6 class="mb-1"><strong><?= htmlspecialchars($cert['TIT_EVE_CUR']) ?></strong></h6>
                                                <small class="text-muted">
                                                    <?= $cert['HORAS_TOTALES'] ?? 'N/A' ?> horas | 
                                                    <?= date('d/m/Y', strtotime($cert['FEC_INI_EVE_CUR'])) ?> - <?= date('d/m/Y', strtotime($cert['FEC_FIN_EVE_CUR'])) ?> |
                                                    <span class="badge" style="background-color: #d4edda; color: #155724;">Completado</span>
                                                </small>
                                            </div>
                                            <button type="button"
                                               class="btn btn-sm btn-outline-danger"
                                               style="--bs-btn-color: var(--primary); --bs-btn-border-color: var(--primary); --bs-btn-hover-bg: var(--primary); --bs-btn-hover-color: #fff;"
                                               title="Ver Certificado"
                                               data-bs-toggle="modal" 
                                               data-bs-target="#certificadoModal"
                                               data-ruta-certificado="../<?= htmlspecialchars($cert['RUTA_CERTIFICADO']) ?>"
                                               data-titulo-evento="<?= htmlspecialchars($cert['TIT_EVE_CUR']) ?>">
                                                <i class="fas fa-file-pdf me-1"></i> Ver
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        </div>
                    </div>

                    <!-- ===== DOCUMENTOS PERSONALES ===== -->
                    <div class="card mt-4">
                        <div class="card-header-custom">
                            <i class="fas fa-file-alt me-2"></i> Mis Documentos Personales
                        </div>
                        <div class="card-body-custom">
                            <p class="text-muted mb-3">Sube documentos requeridos comúnmente para eventos. Estos se usarán automáticamente en inscripciones si coinciden con los requisitos.</p>
                            
                            <?php
                            // Obtener requisitos de tipo DOCUMENTO (puedes limitar a comunes con WHERE NOM_REQ IN ('Cédula', 'Certificado de Matrícula'))
                            $req_docs = $conn->query("SELECT * FROM REQUISITOS WHERE TIPO = 'DOCUMENTO' ORDER BY NOM_REQ")->fetch_all(MYSQLI_ASSOC);
                            
                            foreach ($req_docs as $req): 
                                // Verificar si ya tiene subido
                                $stmt_doc = $conn->prepare("SELECT * FROM usuarios_documentos WHERE CED_USU = ? AND ID_REQ = ?");
                                $stmt_doc->bind_param("si", $cedula, $req['ID_REQ']);
                                $stmt_doc->execute();
                                $doc_existente = $stmt_doc->get_result()->fetch_assoc();
                            ?>
                                <div class="mb-4 border-bottom pb-3">
                                    <h6><?= htmlspecialchars($req['NOM_REQ']) ?></h6>
                                    <?php if ($doc_existente): ?>
                                        <p class="text-success"><i class="fas fa-check-circle"></i> Ya subido: <?= htmlspecialchars($doc_existente['NOMBRE_ARCHIVO']) ?> (Subido el <?= date('d/m/Y', strtotime($doc_existente['FECHA_SUBIDA'])) ?>)</p>
                                        <a href="./<?= htmlspecialchars($doc_existente['URL_ARCHIVO']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Ver</a>
                                    <?php else: ?>
                                        <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> No subido aún.</p>
                                    <?php endif; ?>
                                    
                                    <!-- Formulario para subir/reemplazar -->
                                    <form method="POST" enctype="multipart/form-data" action="procesar_documento_perfil.php">
                                        <input type="hidden" name="id_req" value="<?= $req['ID_REQ'] ?>">
                                        <div class="mb-2">
                                            <label class="form-label">Subir/Reemplazar (PDF/JPG/PNG, máx 5MB):</label>
                                            <input type="file" class="form-control" name="documento" accept=".pdf,.jpg,.jpeg,.png" required>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload"></i> <?= $doc_existente ? 'Reemplazar' : 'Subir' ?></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="certificadoModal" tabindex="-1" aria-labelledby="certificadoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary); color: white;">
                    <h5 class="modal-title" id="certificadoModalLabel">Certificado de Evento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
    /* * SCRIPT PARA EL MODAL DE CERTIFICADO (NUEVO)
     */
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
        certificadoModal.addEventListener('hide.bs.modal', function (event) {
            const modalIframe = certificadoModal.querySelector('#certificadoFrame');
            modalIframe.setAttribute('src', '');
        });
    }
</script>

</body>
</html>