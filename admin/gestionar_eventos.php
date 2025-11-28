<?php
// crear_evento.php  (archivo completo listo para pegar)
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Verificar sesión y cédula
if (!isset($_SESSION['correo']) || !isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}
$cedula = $_SESSION['cedula'];

// Utilidades
function slugify($text) {
  $text = iconv('UTF-8','ASCII//TRANSLIT',$text);
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  $text = preg_replace('~^-+|-+$~', '', $text);
  $text = strtolower($text);
  return $text ?: 'evento';
}
function genCodigoEvento($prefix='EVE'){
  return $prefix . '-' . date('Ymd') . '-' . substr(uniqid('', true), -6);
}

/* Cargar datos para el formulario (catálogos) */
$tipos = [];
$resTipos = $conn->query("SELECT ID_TIPO_EVE, NOM_TIPO_EVE FROM TIPOS_EVENTO ORDER BY NOM_TIPO_EVE");
while ($row = $resTipos->fetch_assoc()) $tipos[] = $row;

$carreras = [];
$resCar = $conn->query("SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA");
while ($row = $resCar->fetch_assoc()) $carreras[] = $row;

$requisitos = [];
$resReq = $conn->query("SELECT ID_REQ, NOM_REQ, TIPO FROM REQUISITOS WHERE ACTIVO=1 ORDER BY NOM_REQ");
while ($row = $resReq->fetch_assoc()) $requisitos[] = $row;

/* Modo edición / añadir información */
$idEvento = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
$isEditMode = $idEvento > 0;

// variables por defecto (vacías en modo crear)
$evento = [
  'TIT_EVE_CUR' => '',
  'DES_EVE_CUR' => '',
  'INSCRIPCION_DESDE' => '',
  'INSCRIPCION_HASTA' => '',
  'FEC_INI_EVE_CUR' => '',
  'FEC_FIN_EVE_CUR' => '',
  'MOD_EVE_CUR' => 'Gratis',
  'COS_EVE_CUR' => 0,
  'LUGAR' => '',
  'UBICACION_DETALLE' => '',
  'CAPACIDAD_MAXIMA' => 0,
  'CUPOS_DISPONIBLES' => 0,
  'HORAS_TOTALES' => 0,
  'ID_TIPO_EVE' => null,
  'IMG_EVE_CUR' => null,
  'RESPONSABLE_CED' => null
];

$selectedReqs = [];
$selectedCarr = [];

if ($isEditMode) {
    // Verificar permiso: que la cédula sea responsable o ponente del evento
    $permStmt = $conn->prepare("
      SELECT 1 FROM PERSONAL_EVENTO
      WHERE ID_EVE_CUR = ? AND CED_USU = ? AND (ES_RESPONSABLE = 1 OR UPPER(ROL_EVENTO) = 'PONENTE') LIMIT 1
    ");
    $permStmt->bind_param("is", $idEvento, $cedula);
    $permStmt->execute();
    $permStmt->store_result();
    if ($permStmt->num_rows === 0) {
        // No tiene permiso para modificar/añadir info
        $permStmt->close();
        header("Location: ../index.php?error=no_permiso_evento");
        exit();
    }
    $permStmt->close();

    // Cargar datos del evento
    $stmt = $conn->prepare("SELECT * FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ? LIMIT 1");
    $stmt->bind_param("i", $idEvento);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $evento = $res->fetch_assoc();
    } else {
        header("Location: ../index.php?error=evento_no_encontrado");
        exit();
    }
    $stmt->close();

    // Cargar requisitos asociados al evento
    $rstmt = $conn->prepare("SELECT ID_REQ FROM EVENTOS_REQUISITOS WHERE ID_EVE_CUR = ?");
    $rstmt->bind_param("i", $idEvento);
    $rstmt->execute();
    $rr = $rstmt->get_result();
    while ($row = $rr->fetch_assoc()) $selectedReqs[] = (int)$row['ID_REQ'];
    $rstmt->close();

    // Cargar carreras asociadas
    $cstmt = $conn->prepare("SELECT ID_CARRERA FROM EVENTOS_CARRERAS WHERE ID_EVE_CUR = ?");
    $cstmt->bind_param("i", $idEvento);
    $cstmt->execute();
    $cr = $cstmt->get_result();
    while ($row = $cr->fetch_assoc()) $selectedCarr[] = (int)$row['ID_CARRERA'];
    $cstmt->close();
}

// Nota: NO ejecutamos guardar aquí — asumimos que tienes tu endpoint guardarEvento.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= $isEditMode ? 'Añadir información · ' . htmlspecialchars($evento['TIT_EVE_CUR']) : 'Crear nuevo evento' ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
      top: 0;
      left: 0;
      width: 260px;
      height: 100vh;
      background: var(--primary);
      color: white;
      padding: 25px 0;
      box-shadow: 5px 0 20px rgba(0, 0, 0, 0.15);
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
      border: 5px solid rgba(255, 255, 255, 0.25);
      transition: all 0.3s;
    }

    .sidebar .logo img:hover {
      transform: scale(1.08);
      border-color: white;
    }

    .sidebar a {
      color: rgba(255, 255, 255, 0.9);
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

    .sidebar a:hover,
    .sidebar a.active {
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
      justify-content: space-between;
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

    .btn-primary {
      background: var(--primary);
      color: white !important;
      border: none;
      border-radius: 40px;
      font-weight: 600;
      font-size: 0.95rem;
      padding: 12px 26px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 4px 12px rgba(163, 0, 0, 0.25);
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .btn-primary i {
      font-size: 1rem;
      background: white;
      color: var(--primary);
      border-radius: 50%;
      padding: 4px;
      width: 22px;
      height: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background: var(--primary-hover);
      box-shadow: 0 6px 18px rgba(163, 0, 0, 0.35);
      transform: translateY(-2px);
    }

    .btn-primary:hover i {
      background: white;
      color: var(--primary-hover);
    }


    .btn-edit {
      background: transparent;
      color: var(--primary);
      border: 1px solid var(--primary);
      padding: 6px 12px;
      border-radius: 10px;
      transition: all 0.3s;
    }

    .btn-edit:hover {
      background: var(--primary);
      color: white;
    }

    .btn-delete {
      background: transparent;
      color: #dc3545;
      border: 1px solid #dc3545;
      padding: 6px 12px;
      border-radius: 10px;
      transition: all 0.3s;
    }

    .btn-delete:hover {
      background: #dc3545;
      color: white;
    }

    /* Modal */
    .modal-content {
      border-radius: var(--radius);
      border: none;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
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

    .form-control,
    .form-select {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 0.95rem;
      transition: all 0.3s;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(163, 0, 0, 0.15);
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

      .content {
        margin-left: 80px;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="logo"><img src="../images/favico.png" alt="Logo UTA"></div>
  <a href="admin_inicio.php"><i class="fas fa-home me-2"></i> Inicio</a>
  <a href="gestionar_eventos.php" class="active"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
  <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> Gestionar Evidencias</a>
  <a href="verificar_pagos.php"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
  <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> Generación de Certificados</a>
  <a href="editar_usuario.php"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
  <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
  <a href="#"><i class="fas fa-cog me-2"></i> Configuraciones</a>
  <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
</div>

<main class="content">
  <div class="container py-3">
    <div class="brand-header">
      <h2>
        <i class="bi bi-calendar2-plus me-2"></i>
        <?= $isEditMode ? 'Añadir información: ' . htmlspecialchars($evento['TIT_EVE_CUR']) : 'Crear nuevo evento o curso' ?>
      </h2>
      <p class="text-muted"><?= $isEditMode ? 'Edita información adicional del evento seleccionado (título no editable).' : 'Completa la información paso a paso' ?></p>
    </div>

    <form method="post" id="formEvento" enctype="multipart/form-data">
      <!-- IMPORTANT: incluimos un hidden con id_evento para que guardarEvento.php pueda manejar edición -->
      <?php if ($isEditMode): ?>
        <input type="hidden" name="ID_EVE_CUR" value="<?= (int)$idEvento ?>">
      <?php endif; ?>

      <div class="accordion" id="eventWizard">

        <!-- Paso 1: Datos básicos -->
        <div class="accordion-item card mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1" aria-expanded="true">
              <i class="bi bi-info-circle me-2"></i>1) Datos básicos
            </button>
          </h2>
          <div id="p1" class="accordion-collapse collapse show">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">Título *</label>
                  <input id="tituloEvento" name="TIT_EVE_CUR" class="form-control" required
                         value="<?= htmlspecialchars($evento['TIT_EVE_CUR']) ?>"
                         <?= $isEditMode ? 'readonly' : '' ?>>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Tipo de evento *</label>
                  <div class="input-group">
                    <select id="tipoEvento" name="ID_TIPO_EVE" class="form-select" required>
                      <option value="">-- Selecciona --</option>
                      <?php foreach($tipos as $t): ?>
                        <option value="<?= (int)$t['ID_TIPO_EVE'] ?>" <?= ((int)$evento['ID_TIPO_EVE'] === (int)$t['ID_TIPO_EVE']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($t['NOM_TIPO_EVE']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalNuevoTipo">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label">Descripción</label>
                  <textarea id="descripcionEvento" name="DES_EVE_CUR" rows="3" class="form-control" placeholder="Breve descripción del evento"><?= htmlspecialchars($evento['DES_EVE_CUR']) ?></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Paso 2: Fechas, lugar y modalidad -->
        <div class="accordion-item card mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="true">
              <i class="bi bi-geo-alt me-2"></i>2) Fechas, lugar y modalidad
            </button>
          </h2>
          <div id="p2" class="accordion-collapse collapse show">
            <div class="accordion-body">
              <div class="mb-3">
                <label class="form-label">Inscripción desde</label>
                <input id="insDesde" type="date" name="INSCRIPCION_DESDE" class="form-control" value="<?= htmlspecialchars($evento['INSCRIPCION_DESDE']) ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Inscripción hasta</label>
                <input id="insHasta" type="date" name="INSCRIPCION_HASTA" class="form-control" value="<?= htmlspecialchars($evento['INSCRIPCION_HASTA']) ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Inicio *</label>
                <input id="fecInicio" type="date" name="FEC_INI_EVE_CUR" class="form-control" value="<?= htmlspecialchars($evento['FEC_INI_EVE_CUR']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Fin *</label>
                <input id="fecFin" type="date" name="FEC_FIN_EVE_CUR" class="form-control" value="<?= htmlspecialchars($evento['FEC_FIN_EVE_CUR']) ?>" required>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Lugar</label>
                  <input id="lugar" name="LUGAR" class="form-control" placeholder="Auditorio FISEI" value="<?= htmlspecialchars($evento['LUGAR']) ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Ubicación/detalle</label>
                  <input id="detalleLugar" name="UBICACION_DETALLE" class="form-control" placeholder="Bloque B, 2do piso" value="<?= htmlspecialchars($evento['UBICACION_DETALLE']) ?>">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Modalidad *</label>
                  <select id="modalidad" name="MOD_EVE_CUR" class="form-select">
                    <option value="Gratis" <?= $evento['MOD_EVE_CUR'] === 'Gratis' ? 'selected' : '' ?>>Gratis</option>
                    <option value="Pagado" <?= $evento['MOD_EVE_CUR'] === 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Costo ($)</label>
                  <input id="costo" type="number" step="0.01" name="COS_EVE_CUR" class="form-control" value="<?= htmlspecialchars($evento['COS_EVE_CUR']) ?>" <?= $evento['MOD_EVE_CUR'] === 'Gratis' ? 'disabled' : '' ?>>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Capacidad máxima</label>
                  <input id="capacidad" type="number" name="CAPACIDAD_MAXIMA" class="form-control" value="<?= (int)$evento['CAPACIDAD_MAXIMA'] ?>" min="0">
                  <div class="muted">Inicializa cupos disponibles.</div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Horas totales</label>
                  <input id="horas" type="number" name="HORAS_TOTALES" class="form-control" min="0" value="<?= (int)$evento['HORAS_TOTALES'] ?>">
                </div>

                <!-- Eliminamos campo 'Responsable' en UI, si necesitas mantenerlo envíalo como hidden -->
                <?php if (!empty($evento['RESPONSABLE_CED'])): ?>
                  <input type="hidden" name="RESPONSABLE_CED" value="<?= htmlspecialchars($evento['RESPONSABLE_CED']) ?>">
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Paso 3: Requisitos -->
        <div class="accordion-item card mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p3" aria-expanded="true">
              <i class="bi bi-list-check me-2"></i>3) Requisitos del evento
            </button>
          </h2>
          <div id="p3" class="accordion-collapse collapse show">
            <div class="accordion-body">
              <div id="tablaRequisitosContainer">
                <p class="muted mb-2">Selecciona los requisitos obligatorios para el evento.</p>
                <div class="table-responsive">
                  <table class="table table-hover align-middle" id="tablaRequisitos">
                    <thead>
                      <tr><th>Sel</th><th>Requisito</th></tr>
                    </thead>
                    <tbody>
                      <?php foreach($requisitos as $r): $checked = in_array((int)$r['ID_REQ'], $selectedReqs); ?>
                        <tr>
                          <td><input class="reqCheck" type="checkbox" name="REQ_ID[]" value="<?= (int)$r['ID_REQ'] ?>" <?= $checked ? 'checked' : '' ?>></td>
                          <td><?= htmlspecialchars($r['NOM_REQ']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Paso 4: Carreras -->
        <div class="accordion-item card mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p4" aria-expanded="true">
              <i class="bi bi-mortarboard me-2"></i>4) Carreras destinatarias
            </button>
          </h2>
          <div id="p4" class="accordion-collapse collapse show">
            <div class="accordion-body">
              <div class="row">
                <?php foreach($carreras as $c): $checkedC = in_array((int)$c['ID_CARRERA'], $selectedCarr); ?>
                  <div class="col-md-4 mb-2">
                    <label class="req-chip">
                      <input class="carCheck" type="checkbox" name="CARRERAS[]" value="<?= (int)$c['ID_CARRERA'] ?>" <?= $checkedC ? 'checked' : '' ?>>
                      <?= htmlspecialchars($c['NOMBRE_CARRERA']) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="muted mt-2">Si no seleccionas ninguna, el evento se considera abierto al público.</div>
            </div>
          </div>
        </div>

        <!-- Paso 5: Imagen del evento -->
        <div class="accordion-item card mb-3">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p5" aria-expanded="true">
              <i class="bi bi-image me-2"></i>5) Imagen del evento
            </button>
          </h2>
          <div id="p5" class="accordion-collapse collapse show">
            <div class="accordion-body d-flex gap-3 align-items-start flex-wrap">
              <div>
                <input id="imgEvento" name="IMG_EVE_CUR" type="file" class="form-control" accept="image/*">
                <small class="text-muted">Formato: jpg, png, webp. Si subes una nueva imagen reemplazará la existente.</small>
              </div>

              <?php if (!empty($evento['IMG_EVE_CUR'])): ?>
                <?php
                  // Mostrar imagen actual (ruta en DB puede ser 'images/eventos/...' o similar)
                  $imgPath = file_exists(__DIR__ . '/../' . $evento['IMG_EVE_CUR']) ? '../' . $evento['IMG_EVE_CUR'] : (filter_var($evento['IMG_EVE_CUR'], FILTER_VALIDATE_URL) ? $evento['IMG_EVE_CUR'] : null);
                ?>
                <?php if ($imgPath): ?>
                  <div>
                    <label class="form-label">Imagen actual</label><br>
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="Imagen evento" class="img-preview">
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Botón final -->
        <div class="card sticky-actions">
          <div class="text-end">
            <button id="btnGuardarEvento" type="submit" class="btn btn-uta px-4 py-2">
              <i class="bi bi-save me-1"></i>
              <?= $isEditMode ? 'Guardar cambios / Añadir información' : 'Guardar evento' ?>
            </button>
          </div>
        </div>

      </div> <!-- accordion -->
    </form>
  </div>
</main>

<!-- Modal Nuevo Tipo (igual que antes) -->
<div class="modal fade" id="modalNuevoTipo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-3">
      <div class="modal-header border-0">
        <h5 class="modal-title text-uta"><i class="bi bi-plus-circle me-2"></i>Nuevo tipo de evento</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nombre del tipo</label><input id="nuevoTipoNombre" class="form-control"></div>
        <div class="mb-4">
          <label class="form-label">Requisitos existentes</label>
          <div id="listaRequisitosExistentes" class="border rounded p-2" style="max-height: 180px; overflow-y:auto;"></div>
        </div>
        <hr>
        <label class="form-label">Crear requisitos nuevos</label>
        <div id="contenedorRequisitos">
          <div class="requisito-item mb-2 d-flex gap-2 align-items-center">
            <input type="text" class="form-control req-nombre" placeholder="Nombre del requisito">
            <select class="form-select req-tipo"><option value="NUMERICO">Numérico</option><option value="TEXTO_CORTO">Texto corto</option><option value="DOCUMENTO">Documento</option></select>
            <input type="number" class="form-control req-valor-min" placeholder="Valor mínimo" style="display:none;">
            <button type="button" class="btn btn-danger btn-remove-requisito">X</button>
          </div>
        </div>
        <button type="button" id="agregarRequisito" class="btn btn-sm btn-uta mt-2">Agregar requisito</button>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button id="guardarNuevoTipo" class="btn btn-uta">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tipoEventoSelect = document.getElementById('tipoEvento');
  const formEvento = document.getElementById('formEvento');
  const btnGuardarEvento = document.getElementById('btnGuardarEvento');

  const contenedorReqModal = document.getElementById('contenedorRequisitos');
  const btnGuardarNuevoTipo = document.getElementById('guardarNuevoTipo');

  const modalidad = document.getElementById('modalidad');
  const costo = document.getElementById('costo');
  const insDesde = document.getElementById('insDesde');
  const insHasta = document.getElementById('insHasta');
  const fecInicio = document.getElementById('fecInicio');
  const fecFin = document.getElementById('fecFin');

  // Fecha mínima
  const hoy = new Date().toISOString().split('T')[0];
  if (insDesde) insDesde.setAttribute('min', hoy);
  if (insHasta) insHasta.setAttribute('min', hoy);

  // Modal requisitos nuevo tipo: cargar requisitos existentes
  const modalNuevoTipo = document.getElementById('modalNuevoTipo');
  if (modalNuevoTipo) {
    modalNuevoTipo.addEventListener('show.bs.modal', () => {
      fetch('obtenerRequisitos.php')
        .then(res => res.json())
        .then(data => {
          const lista = document.getElementById('listaRequisitosExistentes');
          lista.innerHTML = '';
          if (!Array.isArray(data) || data.length === 0) {
            lista.innerHTML = '<p class="text-muted text-center">No existen requisitos registrados.</p>';
            return;
          }
          data.forEach(req => {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `<input class="form-check-input req-existente" type="checkbox" value="${req.ID_REQ}">
                             <label class="form-check-label">${req.NOM_REQ}</label>`;
            lista.appendChild(div);
          });
        })
        .catch(err => console.error('Error cargando requisitos existentes:', err));
    });
  }

  // Guardar nuevo tipo (modal) - comportamiento igual a tu lógica original
  if (btnGuardarNuevoTipo) {
    btnGuardarNuevoTipo.addEventListener('click', async () => {
      const nombre = document.getElementById('nuevoTipoNombre').value.trim();
      if (!nombre) return alert('Debes ingresar el nombre del tipo.');
      const formData = new FormData();
      formData.append('nombre_tipo', nombre);

      // Requisitos nuevos
      const requisitos = [];
      contenedorReqModal.querySelectorAll('.requisito-item').forEach(item => {
        const nom = item.querySelector('.req-nombre').value.trim();
        const tipo = item.querySelector('.req-tipo').value;
        const valMin = item.querySelector('.req-valor-min').value || null;
        if (nom) requisitos.push({ nombre: nom, tipo, valor_min: tipo === 'NUMERICO' ? valMin : null });
      });
      formData.append('requisitos', JSON.stringify(requisitos));

      // Requisitos existentes marcados
      const existentes = [];
      document.querySelectorAll('.req-existente:checked').forEach(cb => existentes.push(cb.value));
      formData.append('requisitos_existentes', JSON.stringify(existentes));

      try {
        const res = await fetch('guardarTipoEvento.php', { method: 'POST', body: formData });
        const json = await res.json();
        if (json.success) {
          alert('Tipo de evento guardado.');
          location.reload();
        } else {
          alert('Error: ' + (json.message || 'No se pudo guardar.'));
        }
      } catch (e) {
        console.error(e);
        alert('Error inesperado al guardar el tipo de evento.');
      }
    });
  }

  // Cambiar costo según modalidad
  if (modalidad) modalidad.addEventListener('change', () => {
    if (modalidad.value === 'Gratis') { costo.value = 0; costo.disabled = true; } else { costo.disabled = false; }
  });

  // Cargar requisitos por tipo (si tienes ese endpoint)
  if (tipoEventoSelect) {
    tipoEventoSelect.addEventListener('change', () => {
      const idTipo = tipoEventoSelect.value;
      if (!idTipo) return;
      fetch('obtenerRequisitosPorTipo.php?id_tipo=' + idTipo)
        .then(res => res.json())
        .then(data => {
          const tbody = document.querySelector('#tablaRequisitos tbody');
          tbody.innerHTML = '';
          if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay requisitos asociados.</td></tr>';
            return;
          }
          data.forEach(req => {
            const checked = document.querySelector(`input.reqCheck[value="${req.ID_REQ}"]`) ? 'checked' : '';
            tbody.innerHTML += `<tr>
              <td><input class="reqCheck" type="checkbox" name="REQ_ID[]" value="${req.ID_REQ}" ${checked}></td>
              <td>${req.NOM_REQ}</td>
            </tr>`;
          });
        })
        .catch(err => console.error('Error cargando requisitos por tipo:', err));
    });
  }

  // Guardar evento: usamos la misma llamada que tenías (fetch a guardarEvento.php).
  if (formEvento && btnGuardarEvento) {
    btnGuardarEvento.addEventListener('click', async (e) => {
      e.preventDefault();

      // Validaciones de fecha simples
      if (insDesde.value && insHasta.value && insHasta.value < insDesde.value) return alert('La fecha de inscripción hasta no puede ser antes de la fecha de inicio de inscripción.');
      if (fecInicio.value && insHasta.value && fecInicio.value < insHasta.value) return alert('La fecha de inicio del evento no puede ser antes de la fecha de fin de inscripción.');
      if (fecFin.value && fecInicio.value && fecFin.value < fecInicio.value) return alert('La fecha de fin no puede ser antes de inicio.');

      const data = new FormData(formEvento);
      // Agregar requisitos seleccionados y carreras seleccionadas ya lo maneja el HTML por los checkboxes
      try {
        const res = await fetch('guardarEvento.php', { method: 'POST', body: data });
        const ct = res.headers.get('content-type') || '';
        const payload = ct.includes('application/json') ? await res.json() : { success:false, message: await res.text() };
        if (payload.success) {
          alert(payload.message || 'Evento guardado correctamente.');
          // Si venimos en modo edición podemos recargar la misma página para ver cambios
          if (<?= $isEditMode ? 'true' : 'false' ?>) {
            location.reload();
          } else {
            formEvento.reset();
          }
        } else {
          alert('Error: ' + (payload.message || 'No se pudo guardar'));
          console.error('Respuesta servidor:', payload);
        }
      } catch (err) {
        console.error('Error al guardar evento:', err);
        alert('Error al guardar el evento.');
      }
    });
  }
});
</script>
</body>
</html>
