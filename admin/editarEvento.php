<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  header("Location: ../index.php"); exit();
}
require_once __DIR__ . '/../includes/conexion.php';

$idEvento = (int)($_GET['id'] ?? 0);
if ($idEvento <= 0) { header("Location: gestionar_eventos.php"); exit(); }

// Evento
$stmt = $conn->prepare("
  SELECT ID_EVE_CUR, TIT_EVE_CUR, DES_EVE_CUR, INSCRIPCION_DESDE, INSCRIPCION_HASTA,
         FEC_INI_EVE_CUR, FEC_FIN_EVE_CUR, MOD_EVE_CUR, COS_EVE_CUR, LUGAR, UBICACION_DETALLE,
         CAPACIDAD_MAXIMA, CUPOS_DISPONIBLES, HORAS_TOTALES, ID_TIPO_EVE, RESPONSABLE_CED, ACTIVO
  FROM EVENTOS_CURSOS WHERE ID_EVE_CUR=?
");
$stmt->bind_param("i", $idEvento);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();
if (!$evento) { header("Location: gestionar_eventos.php"); exit(); }

// Catálogos
$tipos = $conn->query("SELECT ID_TIPO_EVE, NOM_TIPO_EVE FROM TIPOS_EVENTO ORDER BY NOM_TIPO_EVE")->fetch_all(MYSQLI_ASSOC);
$carreras = $conn->query("SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA")->fetch_all(MYSQLI_ASSOC);

// Carreras seleccionadas
$carSel = [];
$r = $conn->prepare("SELECT ID_CARRERA FROM EVENTOS_CARRERAS WHERE ID_EVE_CUR=?");
$r->bind_param("i", $idEvento);
$r->execute();
$rs = $r->get_result();
while($row = $rs->fetch_assoc()) $carSel[] = (int)$row['ID_CARRERA'];

// Requisitos del tipo actual
$reqTipo = $conn->query("
  SELECT r.ID_REQ, r.NOM_REQ
  FROM TIPOS_EVENTO_REQUISITOS tr
  JOIN REQUISITOS r ON r.ID_REQ = tr.ID_REQ
  WHERE tr.ID_TIPO_EVE = ".(int)$evento['ID_TIPO_EVE']."
  ORDER BY r.NOM_REQ
")->fetch_all(MYSQLI_ASSOC);

// Requisitos seleccionados por el evento
$reqSel = [];
$r2 = $conn->prepare("SELECT ID_REQ FROM EVENTOS_REQUISITOS WHERE ID_EVE_CUR=?");
$r2->bind_param("i", $idEvento);
$r2->execute();
$rs2 = $r2->get_result();
while($row = $rs2->fetch_assoc()) $reqSel[] = (int)$row['ID_REQ'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Editar Evento</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    :root{--primary:#a30000;--primary-hover:#d51313;--primary-light:#ffebeb;--radius:16px;--shadow:0 8px 25px rgba(0,0,0,.12);}
    body{background:linear-gradient(135deg,#f5f5f5 0%,#e0e0e0 100%);min-height:100vh}
    .container-box{max-width:1100px;margin:30px auto}
    .card{border:0;border-radius:var(--radius);box-shadow:var(--shadow)}
    .card-header{background:var(--primary);color:#fff;font-weight:600}
    .btn-uta{background:var(--primary);color:#fff;border:none;border-radius:10px}
    .btn-uta:hover{background:var(--primary-hover)}
    .table thead{background:var(--primary);color:#fff}
  </style>
</head>
<body>
<div class="container container-box">
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-pen-to-square me-2"></i> Editar Evento</div>
    <div class="card-body">
      <form id="formEditar" method="POST" action="actualizar_evento.php">
        <input type="hidden" name="ID_EVE_CUR" value="<?= (int)$evento['ID_EVE_CUR'] ?>"/>

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Título *</label>
            <input class="form-control" name="TIT_EVE_CUR" required value="<?= htmlspecialchars($evento['TIT_EVE_CUR']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo de evento *</label>
            <select id="tipoEvento" name="ID_TIPO_EVE" class="form-select" required>
              <option value="">-- Selecciona --</option>
              <?php foreach($tipos as $t): ?>
                <option value="<?= (int)$t['ID_TIPO_EVE'] ?>"
                  <?= (int)$t['ID_TIPO_EVE']===(int)$evento['ID_TIPO_EVE']?'selected':'' ?>>
                  <?= htmlspecialchars($t['NOM_TIPO_EVE']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea name="DES_EVE_CUR" rows="3" class="form-control"><?= htmlspecialchars($evento['DES_EVE_CUR'] ?? '') ?></textarea>
          </div>
        </div>

        <hr class="my-4"/>

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Inscripción desde</label>
            <input type="date" name="INSCRIPCION_DESDE" class="form-control" value="<?= htmlspecialchars($evento['INSCRIPCION_DESDE'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Inscripción hasta</label>
            <input type="date" name="INSCRIPCION_HASTA" class="form-control" value="<?= htmlspecialchars($evento['INSCRIPCION_HASTA'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Inicio *</label>
            <input type="date" name="FEC_INI_EVE_CUR" class="form-control" required value="<?= htmlspecialchars($evento['FEC_INI_EVE_CUR']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Fin *</label>
            <input type="date" name="FEC_FIN_EVE_CUR" class="form-control" required value="<?= htmlspecialchars($evento['FEC_FIN_EVE_CUR']) ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Modalidad *</label>
            <select id="modalidad" name="MOD_EVE_CUR" class="form-select">
              <option value="Gratis" <?= $evento['MOD_EVE_CUR']==='Gratis'?'selected':'' ?>>Gratis</option>
              <option value="Pagado" <?= $evento['MOD_EVE_CUR']==='Pagado'?'selected':'' ?>>Pagado</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Costo ($)</label>
            <input id="costo" type="number" step="0.01" name="COS_EVE_CUR" class="form-control"
                   value="<?= (float)$evento['COS_EVE_CUR'] ?>" <?= $evento['MOD_EVE_CUR']==='Gratis'?'disabled':'' ?>>
          </div>
          <div class="col-md-3">
            <label class="form-label">Capacidad máxima</label>
            <input type="number" name="CAPACIDAD_MAXIMA" class="form-control" min="0" value="<?= (int)$evento['CAPACIDAD_MAXIMA'] ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Cupos disponibles</label>
            <input type="number" name="CUPOS_DISPONIBLES" class="form-control" min="0" value="<?= (int)$evento['CUPOS_DISPONIBLES'] ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Horas totales</label>
            <input type="number" name="HORAS_TOTALES" class="form-control" min="0" value="<?= (int)$evento['HORAS_TOTALES'] ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Lugar</label>
            <input name="LUGAR" class="form-control" value="<?= htmlspecialchars($evento['LUGAR'] ?? '') ?>">
          </div>
          <div class="col-md-5">
            <label class="form-label">Ubicación/detalle</label>
            <input name="UBICACION_DETALLE" class="form-control" value="<?= htmlspecialchars($evento['UBICACION_DETALLE'] ?? '') ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Responsable (Cédula)</label>
            <input name="RESPONSABLE_CED" class="form-control" value="<?= htmlspecialchars($evento['RESPONSABLE_CED'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Activo</label>
            <select name="ACTIVO" class="form-select">
              <option value="1" <?= (int)$evento['ACTIVO']===1?'selected':'' ?>>Sí</option>
              <option value="0" <?= (int)$evento['ACTIVO']===0?'selected':'' ?>>No</option>
            </select>
          </div>
        </div>

        <hr class="my-4"/>

        <div class="mb-3">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-2">Requisitos del evento</h5>
            <small class="text-muted">Se listan según el <b>Tipo de evento</b> seleccionado</small>
          </div>
          <div class="table-responsive">
            <table class="table align-middle" id="tablaRequisitos">
              <thead><tr><th>Sel</th><th>Requisito</th></tr></thead>
              <tbody>
                <?php if (empty($reqTipo)): ?>
                  <tr><td colspan="2" class="text-center text-muted">No hay requisitos para este tipo.</td></tr>
                <?php else: foreach($reqTipo as $r): ?>
                  <tr>
                    <td>
                      <input type="checkbox" class="reqCheck" name="REQ_ID[]"
                             value="<?= (int)$r['ID_REQ'] ?>"
                             <?= in_array((int)$r['ID_REQ'], $reqSel, true) ? 'checked' : '' ?>>
                    </td>
                    <td><?= htmlspecialchars($r['NOM_REQ']) ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mb-3">
          <h5 class="mb-2">Carreras destinatarias</h5>
          <div class="row">
            <?php foreach($carreras as $c): ?>
              <div class="col-md-4 mb-2">
                <label class="d-inline-flex align-items-center gap-2">
                  <input type="checkbox" class="carCheck" name="CARRERAS[]"
                         value="<?= (int)$c['ID_CARRERA'] ?>"
                         <?= in_array((int)$c['ID_CARRERA'],$carSel,true)?'checked':'' ?>>
                  <?= htmlspecialchars($c['NOMBRE_CARRERA']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="text-muted">Si no eliges ninguna, el evento queda abierto al público.</div>
        </div>

        <div class="text-end">
          <button class="btn btn-uta px-4"><i class="fa fa-save me-1"></i> Guardar cambios</button>
          <a class="btn btn-outline-secondary ms-2" href="gestionar_eventos.php"><i class="fa fa-arrow-left me-1"></i> Volver</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Modalidad → costo habilitado/deshabilitado
const modalidad = document.getElementById('modalidad');
const costo = document.getElementById('costo');
if (modalidad){
  const toggle = ()=>{ if (modalidad.value==='Gratis'){costo.value=0;costo.disabled=true;} else {costo.disabled=false;} };
  modalidad.addEventListener('change', toggle); toggle();
}

// Cambiar tipo → recargar requisitos
const tipoSel = document.getElementById('tipoEvento');
tipoSel?.addEventListener('change', ()=>{
  const idTipo = tipoSel.value;
  const tbody = document.querySelector('#tablaRequisitos tbody');
  tbody.innerHTML = '<tr><td colspan="2" class="text-muted">Cargando...</td></tr>';
  fetch('obtenerRequisitosPorTipo.php?id_tipo='+encodeURIComponent(idTipo))
    .then(r=>r.json())
    .then(data=>{
      tbody.innerHTML = '';
      if (!Array.isArray(data) || data.length===0){
        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No hay requisitos para este tipo.</td></tr>';
        return;
      }
      data.forEach(req=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><input type="checkbox" class="reqCheck" name="REQ_ID[]" value="${req.ID_REQ}" checked></td>
          <td>${req.NOM_REQ}</td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(()=>{ tbody.innerHTML = '<tr><td colspan="2" class="text-danger">Error cargando requisitos.</td></tr>'; });
});
</script>
</body>
</html>
