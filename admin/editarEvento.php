<?php
// editar_evento.php - versión corregida
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Verificar sesión
if (!isset($_SESSION['correo']) || !isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

$cedula = $_SESSION['cedula'];
$idEvento = (int)($_GET['id'] ?? 0);
if ($idEvento <= 0) { header("Location: gestionar_eventos.php"); exit(); }

// =========================================================
//   Cargar datos del evento
// =========================================================
$stmt = $conn->prepare("
  SELECT ID_EVE_CUR, TIT_EVE_CUR, DES_EVE_CUR, INSCRIPCION_DESDE, INSCRIPCION_HASTA,
         FEC_INI_EVE_CUR, FEC_FIN_EVE_CUR, MOD_EVE_CUR, COS_EVE_CUR, 
         LUGAR, UBICACION_DETALLE, CAPACIDAD_MAXIMA, CUPOS_DISPONIBLES,
         HORAS_TOTALES, ID_TIPO_EVE, RESPONSABLE_CED, ACTIVO, IMG_EVE_CUR
  FROM EVENTOS_CURSOS 
  WHERE ID_EVE_CUR=?
");
$stmt->bind_param("i", $idEvento);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if (!$evento) { header("Location: gestionar_eventos.php"); exit(); }

// =========================================================
//   Catálogos
// =========================================================
$tipos = $conn->query("SELECT ID_TIPO_EVE, NOM_TIPO_EVE FROM TIPOS_EVENTO ORDER BY NOM_TIPO_EVE")->fetch_all(MYSQLI_ASSOC);
$carreras = $conn->query("SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA")->fetch_all(MYSQLI_ASSOC);

// =========================================================
//   Carreras seleccionadas
// =========================================================
$carSel = [];
$q = $conn->prepare("SELECT ID_CARRERA FROM EVENTOS_CARRERAS WHERE ID_EVE_CUR=?");
$q->bind_param("i", $idEvento);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $carSel[] = (int)$row['ID_CARRERA'];
}

$requisitosInscripcion = $conn->query("
    SELECT ID_REQ, NOM_REQ, DES_REQ, TIPO, VALOR_MINIMO
    FROM REQUISITOS 
    WHERE TIPO_REQUISITO = 'INSCRIPCION' 
      AND ACTIVO = 1
    ORDER BY NOM_REQ
")->fetch_all(MYSQLI_ASSOC);

$requisitosAprobacion = $conn->query("
    SELECT ID_REQ, NOM_REQ, DES_REQ, TIPO, VALOR_MINIMO
    FROM REQUISITOS 
    WHERE TIPO_REQUISITO = 'APROBACION' 
      AND ACTIVO = 1
    ORDER BY NOM_REQ
")->fetch_all(MYSQLI_ASSOC);

// =========================================================
//   CARGAR REQUISITOS YA SELECCIONADOS PARA ESTE EVENTO
// =========================================================
$selectedReqs = [];
$valoresMinimos = [];

$r = $conn->prepare("
    SELECT ID_REQ, VALOR_MINIMO_APROBATORIO
    FROM EVENTOS_REQUISITOS 
    WHERE ID_EVE_CUR=?
");
$r->bind_param("i", $idEvento);
$r->execute();
$rs = $r->get_result();

while ($row = $rs->fetch_assoc()) {
    $idReq = (int)$row['ID_REQ'];
    $selectedReqs[] = $idReq;
    
    if ($row['VALOR_MINIMO_APROBATORIO'] !== null) {
        $valoresMinimos[$idReq] = $row['VALOR_MINIMO_APROBATORIO'];
    }
}
$isEditMode = true; // <--- agregado para que tu botón no dé error
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Editar Evento</title>
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
      overflow-y: auto;
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

    .brand-header {
      background: white;
      padding: 25px 30px;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      margin-bottom: 30px;
    }

    .brand-header h2 {
      margin: 0 0 10px 0;
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--dark);
    }

    .card {
      background: white;
      border: none;
      margin-bottom: 20px;
    }

    .accordion-button {
      background: var(--primary);
      color: white;
      font-weight: 600;
      font-size: 1.1rem;
    }

    .accordion-button:not(.collapsed) {
      background: var(--primary-hover);
      color: white;
    }

    .accordion-button:focus {
      box-shadow: none;
      border-color: var(--primary);
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

    .btn-uta {
      background: var(--primary);
      color: white;
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
    }

    .btn-uta:hover {
      background: var(--primary-hover);
      box-shadow: 0 6px 18px rgba(163, 0, 0, 0.35);
      transform: translateY(-2px);
    }

    .req-chip {
      display: inline-flex;
      align-items: center;
      background: var(--gray-light);
      border: 2px solid #dee2e6;
      border-radius: 20px;
      padding: 8px 16px;
      margin: 5px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .req-chip:hover {
      border-color: var(--primary);
      background: var(--primary-light);
    }

    .req-chip input[type="checkbox"] {
      margin-right: 8px;
    }

    .req-chip input[type="checkbox"]:checked + span {
      font-weight: 600;
      color: var(--primary);
    }

    .requisitos-section {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 15px;
    }

    .requisitos-section h6 {
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .requisitos-section h6 i {
      font-size: 1.2rem;
    }

    .badge-tipo-req {
      font-size: 0.75rem;
      padding: 4px 10px;
      border-radius: 12px;
      font-weight: 500;
    }

    .badge-inscripcion {
      background: #e3f2fd;
      color: #1976d2;
    }

    .badge-aprobacion {
      background: #f3e5f5;
      color: #7b1fa2;
    }

    .sticky-actions {
      position: sticky;
      bottom: 20px;
      background: white;
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      z-index: 100;
    }

    .img-preview {
      max-width: 200px;
      max-height: 200px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .muted {
      font-size: 0.85rem;
      color: var(--gray);
      margin-top: 5px;
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
  <a href="admin_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
  <a href="gestionar_eventos.php" class="active"><i class="fas fa-calendar-check"></i> <span>Gestionar Eventos</span></a>
  <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> <span>Gestionar Evidencias</span></a>
  <a href="verificar_pagos.php"><i class="fa fa-credit-card"></i> <span>Gestionar Pagos</span></a>
  <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> <span>Certificados</span></a>
  <a href="editar_usuario.php"><i class="fas fa-users"></i> <span>Gestionar Usuarios</span></a>
  <a href="perfil.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
  <a href="#"><i class="fas fa-cog"></i> <span>Configuraciones</span></a>
  <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
</div>

<div class="content">
  <div class="brand-header mb-4">
    <h2>Editar Evento: <?= htmlspecialchars($evento['TIT_EVE_CUR']) ?></h2>
  </div>

  <div class="card">
    <div class="card-body">

      <form id="formEditar" method="POST" action="actualizar_evento.php" enctype="multipart/form-data">

        <input type="hidden" name="ID_EVE_CUR" value="<?= $evento['ID_EVE_CUR'] ?>">

        <div class="accordion" id="eventWizard">
<div class="accordion-item card">
    <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p1">
            1) Datos básicos
        </button>
    </h2>
    <div id="p1" class="accordion-collapse collapse show">
        <div class="accordion-body">

            <label>Título</label>
            <input name="TIT_EVE_CUR" class="form-control" value="<?= htmlspecialchars($evento['TIT_EVE_CUR']) ?>" readonly>

            <label class="mt-3">Tipo de evento</label>
            <select name="ID_TIPO_EVE" class="form-select" required>
                <option value="">-- Selecciona --</option>
                <?php foreach($tipos as $t): ?>
                <option value="<?= $t['ID_TIPO_EVE'] ?>" 
                    <?= ($evento['ID_TIPO_EVE'] == $t['ID_TIPO_EVE']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['NOM_TIPO_EVE']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label class="mt-3">Descripción</label>
            <textarea name="DES_EVE_CUR" class="form-control" rows="4"><?= htmlspecialchars($evento['DES_EVE_CUR'] ?? "")  ?></textarea>

        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- 2. FECHAS Y MODALIDAD  (NO SE MODIFICÓ TU DISEÑO) -->
<!-- ========================================================= -->
<?php /* NO CAMBIÉ NADA */ ?>
<div class="accordion-item card">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2">
            2) Fechas, lugar y modalidad
        </button>
    </h2>
    <div id="p2" class="accordion-collapse collapse show">
        <div class="accordion-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label>Inscripción desde</label>
                    <input type="date" name="INSCRIPCION_DESDE" class="form-control"
                    value="<?= $evento['INSCRIPCION_DESDE'] ?>">
                </div>

                <div class="col-md-6">
                    <label>Inscripción hasta</label>
                    <input type="date" name="INSCRIPCION_HASTA" class="form-control"
                    value="<?= $evento['INSCRIPCION_HASTA'] ?>">
                </div>

                <div class="col-md-6">
                    <label>Fecha inicio *</label>
                    <input type="date" name="FEC_INI_EVE_CUR" required class="form-control"
                    value="<?= $evento['FEC_INI_EVE_CUR'] ?>">
                </div>

                <div class="col-md-6">
                    <label>Fecha fin *</label>
                    <input type="date" name="FEC_FIN_EVE_CUR" required class="form-control"
                    value="<?= $evento['FEC_FIN_EVE_CUR'] ?>">
                </div>

                <div class="col-md-6">
    <label>Lugar</label>
    <input name="LUGAR" class="form-control" value="<?= htmlspecialchars($evento['LUGAR'] ?? '') ?>">
</div>

                <div class="col-md-6">
    <label>Ubicación detallada</label>
    <input name="UBICACION_DETALLE" class="form-control"
    value="<?= htmlspecialchars($evento['UBICACION_DETALLE'] ?? '') ?>">
</div>

                <div class="col-md-3">
                    <label>Modalidad</label>
                    <select name="MOD_EVE_CUR" id="modalidad" class="form-select">
                        <option value="Gratis" <?= $evento['MOD_EVE_CUR']=='Gratis'?'selected':'' ?>>Gratis</option>
                        <option value="Pagado" <?= $evento['MOD_EVE_CUR']=='Pagado'?'selected':'' ?>>Pagado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Costo</label>
                    <input name="COS_EVE_CUR" id="costo" type="number" step="0.01"
                           class="form-control"
                           value="<?= $evento['COS_EVE_CUR'] ?>"
                           <?= $evento['MOD_EVE_CUR']=='Gratis'?'disabled':'' ?>>
                </div>

                <div class="col-md-3">
                    <label>Capacidad máxima</label>
                    <input name="CAPACIDAD_MAXIMA" type="number" class="form-control"
                    value="<?= $evento['CAPACIDAD_MAXIMA'] ?>">
                </div>

                <div class="col-md-3">
                    <label>Horas totales</label>
                    <input name="HORAS_TOTALES" type="number" class="form-control"
                    value="<?= $evento['HORAS_TOTALES'] ?>">
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- 3. REQUISITOS  (AQUÍ SOLO CORREGÍ VARIABLES, NO TU DISEÑO) -->
<!-- ========================================================= -->
<!-- ========================================================= -->
<!-- 3. REQUISITOS DEL EVENTO — CORREGIDO -->
<!-- ========================================================= -->

<div class="accordion-item card">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p3">
            3) Requisitos del evento
        </button>
    </h2>

    <div id="p3" class="accordion-collapse collapse show">
        <div class="accordion-body">

            <!-- ====================================== -->
            <!--     REQUISITOS DE INSCRIPCIÓN          -->
            <!-- ====================================== -->
            <div class="requisitos-section">
                <h6>
                    <i class="bi bi-clipboard-check"></i>
                    Requisitos de Inscripción
                </h6>

                <div class="row">
                    <?php if(empty($requisitosInscripcion)): ?>
                        <div class="alert alert-info w-100">
                            <i class="bi bi-info-circle"></i> 
                            No hay requisitos de inscripción configurados.
                        </div>
                    <?php else: ?>
                        <?php foreach($requisitosInscripcion as $r): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body p-3">

                                    <label class="d-flex align-items-center mb-2">
                                        <input type="checkbox"
                                            name="REQ_INSCRIPCION[]"
                                            value="<?= $r['ID_REQ'] ?>"
                                            class="me-2"
                                            <?= in_array($r['ID_REQ'], $selectedReqs) ? 'checked' : '' ?>>
                                        <strong><?= htmlspecialchars($r['NOM_REQ']) ?></strong>
                                    </label>

                                    <!-- Si el requisito es numérico -->
                                    <?php if ($r['TIPO'] === 'NUMERICO'): ?>
                                    <div>
                                        <label class="form-label small text-muted">
                                            Valor mínimo requerido:
                                        </label>
                                        <input type="number"
                                            step="0.01"
                                            name="VALOR_MIN[<?= $r['ID_REQ'] ?>]"
                                            class="form-control form-control-sm"
                                            value="<?= $valoresMinimos[$r['ID_REQ']] ?? '' ?>">
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-4">

            <!-- ====================================== -->
            <!--     REQUISITOS DE APROBACIÓN           -->
            <!-- ====================================== -->
            <div class="requisitos-section">
                <h6>
                    <i class="bi bi-award"></i>
                    Requisitos de Aprobación
                </h6>

                <div class="row">
                    <?php if(empty($requisitosAprobacion)): ?>
                        <div class="alert alert-info w-100">
                            <i class="bi bi-info-circle"></i> 
                            No hay requisitos de aprobación configurados.
                        </div>
                    <?php else: ?>
                        <?php foreach($requisitosAprobacion as $r): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body p-3">

                                    <label class="d-flex align-items-center mb-2">
                                        <input type="checkbox"
                                            name="REQ_APROBACION[]"
                                            value="<?= $r['ID_REQ'] ?>"
                                            class="me-2"
                                            <?= in_array($r['ID_REQ'], $selectedReqs) ? 'checked' : '' ?>>
                                        <strong><?= htmlspecialchars($r['NOM_REQ']) ?></strong>
                                    </label>

                                    <!-- Si el requisito es numérico -->
                                    <?php if ($r['TIPO'] === 'NUMERICO'): ?>
                                    <div>
                                        <label class="form-label small text-muted">
                                            Valor mínimo aprobatorio:
                                        </label>
                                        <input type="number"
                                            step="0.01"
                                            name="VALOR_MIN_APR[<?= $r['ID_REQ'] ?>]"
                                            class="form-control form-control-sm"
                                            value="<?= $valoresMinimos[$r['ID_REQ']] ?? '' ?>">
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- ========================================================= -->
<!-- 4. CARRERAS (NO MODIFIQUÉ NADA, SOLO VARIABLES) -->
<!-- ========================================================= -->
<div class="accordion-item card">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p4">
            4) Carreras destinatarias
        </button>
    </h2>

    <div id="p4" class="accordion-collapse collapse show">
        <div class="accordion-body">
            <div class="row">
                <?php foreach($carreras as $c): ?>
                <div class="col-md-4 mb-2">
                    <label class="req-chip w-100">
                        <input type="checkbox" name="CARRERAS[]" value="<?= $c['ID_CARRERA'] ?>"
                            <?= in_array($c['ID_CARRERA'], $carSel) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($c['NOMBRE_CARRERA']) ?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- 5. IMAGEN DEL EVENTO -->
<!-- ========================================================= -->
<div class="accordion-item card">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p5">
            5) Imagen del evento
        </button>
    </h2>

    <div id="p5" class="accordion-collapse collapse show">
        <div class="accordion-body">

            <label>Seleccionar imagen</label>
            <input type="file" name="IMG_EVE_CUR" accept="image/*" class="form-control">

           <?php if (!empty($evento['IMG_EVE_CUR'])): ?>
<div class="mt-3">
    <label>Imagen actual</label><br>
    <img src="../<?= htmlspecialchars($evento['IMG_EVE_CUR'] ?? '') ?>" height="150">
</div>
<?php endif; ?>

        </div>
    </div>
</div>

<!-- BOTÓN -->
<div class="text-end mt-3">
    <button type="submit" class="btn btn-danger">
        <i class="bi bi-save"></i> Guardar cambios
    </button>
</div>

</div> <!-- accordion -->

</form>
</div>
</div>
</div> <!-- container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

  const modalidad   = document.getElementById('modalidad');
  const costo       = document.getElementById('costo');
  const insDesde    = document.getElementById('insDesde');
  const insHasta    = document.getElementById('insHasta');
  const fecInicio   = document.getElementById('fecInicio');
  const fecFin      = document.getElementById('fecFin');
  const btnGuardar  = document.getElementById('btnGuardarEvento');
  const formEditar  = document.getElementById('formEditar');

  /* ----------------------------
        FECHAS MÍNIMAS
  ---------------------------- */
  const hoy = new Date().toISOString().split('T')[0];
  if (insDesde) insDesde.min = hoy;
  if (insHasta) insHasta.min = hoy;

  /* ----------------------------
      CAMBIAR COSTO SEGÚN MODALIDAD
  ---------------------------- */
  if (modalidad) {
    modalidad.addEventListener('change', () => {
      if (modalidad.value === 'Gratis') {
        costo.value = 0;
        costo.disabled = true;
      } else {
        costo.disabled = false;
      }
    });
  }

  /* ----------------------------
      VALIDACIONES Y GUARDADO
  ---------------------------- */
  if (btnGuardar && formEditar) {
    btnGuardar.addEventListener('click', (e) => {
      e.preventDefault();

      // ✔ VALIDACIÓN: inscripción hasta >= inscripción desde
      if (insDesde.value && insHasta.value && insHasta.value < insDesde.value) {
        alert("La fecha de inscripción hasta no puede ser antes del inicio.");
        return;
      }

      // ✔ VALIDACIÓN: inicio del evento >= fin inscripción
      if (fecInicio.value && insHasta.value && fecInicio.value < insHasta.value) {
        alert("El evento no puede iniciar antes del fin de inscripción.");
        return;
      }

      // ✔ VALIDACIÓN: fin >= inicio
      if (fecInicio.value && fecFin.value && fecFin.value < fecInicio.value) {
        alert("La fecha de fin no puede ser antes del inicio del evento.");
        return;
      }

      formEditar.submit();
    });
  }

});
</script>


</body>
</html>
