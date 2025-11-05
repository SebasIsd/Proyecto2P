<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  header("Location: ../index.php"); exit();
}
require_once __DIR__ . '/../includes/conexion.php';

/* ====== Filtros ====== */
$eventId = isset($_GET['evento']) && $_GET['evento'] !== '' ? (int)$_GET['evento'] : null;
$tipo    = isset($_GET['tipo'])   && $_GET['tipo']   !== '' ? $_GET['tipo']   : null; // NUMERICO/TEXTO_CORTO/DOCUMENTO
$estado  = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null; // Pendiente/Aprobado/Rechazado
$q       = trim($_GET['q'] ?? ''); // búsqueda por nombre, cédula o requisito

/* Combo de eventos (obligatorio) */
$evRes = $conn->query("SELECT ID_EVE_CUR, TIT_EVE_CUR FROM EVENTOS_CURSOS ORDER BY FEC_INI_EVE_CUR DESC");

/* Estados y tipos para combos */
$tiposOpts   = ['NUMERICO' => 'Numérico', 'TEXTO_CORTO' => 'Texto', 'DOCUMENTO' => 'Documento'];
$estadosOpts = ['Pendiente','Aprobado','Rechazado'];

/* Si no hay evento elegido, no consultamos */
$rows = null; $total = 0; $page=1; $perPage=50;

if ($eventId) {
  $where = ["i.ID_EVE_CUR = ".(int)$eventId];
  if ($tipo)    $where[] = "r.TIPO = '".$conn->real_escape_string($tipo)."'";
  if ($estado)  $where[] = "ev.ESTADO_VALIDACION = '".$conn->real_escape_string($estado)."'";
  if ($q !== '') {
    $qLike = "%".$conn->real_escape_string($q)."%";
    $where[] = "(u.CED_USU LIKE '$qLike' OR CONCAT(u.APE_PRI_USU,' ',u.NOM_PRI_USU) LIKE '$qLike' OR r.NOM_REQ LIKE '$qLike')";
  }
  $sqlWhere = "WHERE ".implode(" AND ", $where);

  $baseSql = "
    SELECT 
      i.ID_INS, i.ID_EVE_CUR, e.TIT_EVE_CUR,
      u.CED_USU, CONCAT(u.APE_PRI_USU,' ',u.NOM_PRI_USU) AS NOMBRE,
      r.ID_REQ, r.NOM_REQ, r.TIPO, r.VALOR_MINIMO,
      ev.VALOR_NUMERICO, ev.VALOR_TEXTO, ev.URL_ARCHIVO, ev.NOMBRE_ARCHIVO, ev.TIPO_MIME,
      ev.ESTADO_VALIDACION, ev.OBSERVACION
    FROM EVIDENCIAS ev
    JOIN INSCRIPCIONES i ON i.ID_INS = ev.ID_INS
    JOIN USUARIOS     u ON u.CED_USU = i.CED_USU
    JOIN REQUISITOS   r ON r.ID_REQ  = ev.ID_REQ
    JOIN EVENTOS_CURSOS e ON e.ID_EVE_CUR = i.ID_EVE_CUR
    $sqlWhere
  ";

  /* Paginación */
  $perPage = 50;
  $page = max(1, (int)($_GET['page'] ?? 1));
  $offset = ($page - 1) * $perPage;

  $total = (int)$conn->query("SELECT COUNT(*) c FROM ($baseSql) t")->fetch_assoc()['c'];
  $rows  = $conn->query($baseSql . " ORDER BY NOMBRE, r.NOM_REQ LIMIT $perPage OFFSET $offset");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Evidencias — Por evento</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    :root{--primary:#a30000;--primary-hover:#d51313;--primary-light:#ffebeb;--radius:16px;--shadow:0 8px 25px rgba(0,0,0,.12);}
    body{background:linear-gradient(135deg,#f5f5f5 0%,#e0e0e0 100%);}
    .sidebar{position:fixed;top:0;left:0;width:260px;height:100vh;background:var(--primary);color:#fff;padding:25px 0;box-shadow:5px 0 20px rgba(0,0,0,.15);}
    .sidebar a{color:#fff;opacity:.9;display:flex;gap:10px;padding:14px 26px;text-decoration:none;border-left:4px solid transparent}
    .sidebar a:hover,.sidebar a.active{background:var(--primary-hover);border-left-color:#fff}
    .content{margin-left:260px;padding:36px}
    .card{border-radius:var(--radius); box-shadow:var(--shadow); border:0}
    .card-header{background:var(--primary); color:#fff; font-weight:600}
    .table thead{background:var(--primary);color:#fff}
    .btn-uta{background:var(--primary);color:#fff;border:none;border-radius:10px}
    .btn-uta:hover{background:var(--primary-hover)}
    .avatar-circle{width:40px;height:40px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700}
    @media (max-width: 768px){.sidebar{width:80px}.content{margin-left:80px}}
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="text-center mb-4">
      <img src="../images/favico.png" alt="Logo" style="width:110px;border-radius:50%;border:4px solid rgba(255,255,255,.3)">
    </div>
    <a href="admin_inicio.php"><i class="fa fa-home"></i><span>Inicio</span></a>
    <a href="n.php"><i class="fa fa-calendar-check"></i><span>Gestionar Eventos</span></a>
    <a href="evidencias_global.php" class="active"><i class="fa fa-clipboard-check"></i><span>Evidencias</span></a>
    <a href="editar_usuario.php"><i class="fa fa-users"></i><span>Usuarios</span></a>
    <a href="#"><i class="fa fa-chart-bar"></i><span>Estadísticas</span></a>
    <a href="perfil.php"><i class="fa fa-user"></i><span>Perfil</span></a>
    <a href="../Login/logout.php"><i class="fa fa-sign-out-alt"></i><span>Salir</span></a>
  </div>

  <div class="content">
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="mb-1">Evidencias por evento</h3>
        <div class="text-muted">Selecciona un evento y busca por participante (cédula o nombre) o por requisito.</div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
      <div class="card-header">Filtros</div>
      <div class="card-body">
        <form id="filtros" class="row g-3" method="get">
          <div class="col-md-5">
            <label class="form-label">Evento <span class="text-danger">*</span></label>
            <select name="evento" id="eventoSel" class="form-select" required>
              <option value="">— Selecciona un evento —</option>
              <?php while($ev = $evRes->fetch_assoc()): ?>
                <option value="<?= (int)$ev['ID_EVE_CUR'] ?>" <?= $eventId===(int)$ev['ID_EVE_CUR']?'selected':'' ?>>
                  <?= htmlspecialchars($ev['TIT_EVE_CUR']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" id="tipoSel" class="form-select" <?= !$eventId ? 'disabled' : '' ?>>
              <option value="">Todos</option>
              <?php foreach($tiposOpts as $k=>$v): ?>
                <option value="<?= $k ?>" <?= $tipo===$k?'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Estado</label>
            <select name="estado" id="estadoSel" class="form-select" <?= !$eventId ? 'disabled' : '' ?>>
              <option value="">Todos</option>
              <?php foreach($estadosOpts as $opt): ?>
                <option value="<?= $opt ?>" <?= $estado===$opt?'selected':'' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Buscar</label>
            <input type="text" name="q" id="buscarInp" value="<?= htmlspecialchars($q) ?>" class="form-control"
                   placeholder="Cédula, nombre o requisito" <?= !$eventId ? 'disabled' : '' ?>>
          </div>
          <!-- no botón: se envía automático -->
        </form>
      </div>
    </div>

    <?php if (!$eventId): ?>
      <div class="alert alert-warning">
        <i class="fa fa-info-circle me-1"></i> Selecciona un <b>evento</b> para listar las evidencias.
      </div>
    <?php else: ?>
      <!-- Tabla -->
      <div class="card">
        <div class="card-header">Resultados (<?= (int)$total ?>)</div>
        <div class="card-body table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Participante</th>
                <th>Requisito</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Archivo</th>
                <th>Estado</th>
                <th>Observación</th>
                <th class="text-center">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($rows && $rows->num_rows===0): ?>
                <tr><td colspan="8" class="text-center text-muted">No hay evidencias que coincidan con los filtros.</td></tr>
              <?php endif; ?>

              <?php if ($rows) while($r = $rows->fetch_assoc()): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar-circle"><?= strtoupper(substr($r['NOMBRE'],0,1)) ?></div>
                      <div>
                        <div class="fw-semibold"><?= htmlspecialchars($r['NOMBRE']) ?></div>
                        <div class="text-muted" style="font-size:.85rem"><?= $r['CED_USU'] ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($r['NOM_REQ']) ?></div>
                    <?php if ($r['TIPO']==='NUMERICO' && $r['VALOR_MINIMO']!==null): ?>
                      <div class="text-muted" style="font-size:.8rem">Mínimo: <b><?= (float)$r['VALOR_MINIMO'] ?></b></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($r['TIPO']==='NUMERICO'): ?>
                      <span class="badge bg-primary">Numérico</span>
                    <?php elseif ($r['TIPO']==='DOCUMENTO'): ?>
                      <span class="badge bg-success">Documento</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Texto</span>
                    <?php endif; ?>
                  </td>
                  <td style="min-width:180px">
                    <form class="form-evidencia" action="evidencia_update.php" method="post">
                      <input type="hidden" name="id_evento" value="<?= (int)$r['ID_EVE_CUR'] ?>">
                      <input type="hidden" name="id_ins" value="<?= (int)$r['ID_INS'] ?>">
                      <input type="hidden" name="id_req" value="<?= (int)$r['ID_REQ'] ?>">
                      <input type="hidden" name="tipo" value="<?= $r['TIPO'] ?>">

                      <?php if ($r['TIPO']==='NUMERICO'): ?>
                        <input type="number" step="0.01" class="form-control"
                               name="valor_numerico"
                               value="<?= $r['VALOR_NUMERICO'] !== null ? (float)$r['VALOR_NUMERICO'] : '' ?>"
                               placeholder="Ingrese nota">
                      <?php elseif ($r['TIPO']==='TEXTO_CORTO'): ?>
                        <input type="text" class="form-control"
                               name="valor_texto"
                               value="<?= htmlspecialchars($r['VALOR_TEXTO'] ?? '') ?>"
                               placeholder="Texto corto">
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                  </td>
                  <td style="min-width:160px">
                    <?php if ($r['TIPO']==='DOCUMENTO' && $r['URL_ARCHIVO']): ?>
                      <a class="btn btn-sm btn-outline-secondary" target="_blank"
                         href="<?= htmlspecialchars($r['URL_ARCHIVO']) ?>">
                        <i class="fa fa-file"></i> Ver documento
                      </a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="min-width:150px">
                    <select name="estado" class="form-select">
                      <?php foreach ($estadosOpts as $opt): ?>
                        <option <?= $r['ESTADO_VALIDACION']===$opt?'selected':'' ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td style="min-width:220px">
                    <textarea name="observacion" class="form-control" rows="1"
                              placeholder="Observación..."><?= htmlspecialchars($r['OBSERVACION'] ?? '') ?></textarea>
                  </td>
                  <td class="text-center">
                      <button class="btn btn-uta btn-sm">
                        <i class="fa fa-save"></i> Guardar
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="card-body pt-0">
          <?php
            if ($eventId) {
              $pages = max(1, ceil($total / $perPage));
              if ($pages > 1):
                echo '<nav><ul class="pagination justify-content-end">';
                for($p=1;$p<=$pages;$p++){
                  $qs = $_GET; $qs['page']=$p; $href='?'.http_build_query($qs);
                  echo '<li class="page-item '.($p===$page?'active':'').'"><a class="page-link" href="'.$href.'">'.$p.'</a></li>';
                }
                echo '</ul></nav>';
              endif;
            }
          ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
    // Autofiltrado: cambia evento/tipo/estado o escribe en buscar -> submit automático (debounce)
    const form   = document.getElementById('filtros');
    const selEv  = document.getElementById('eventoSel');
    const selTip = document.getElementById('tipoSel');
    const selEst = document.getElementById('estadoSel');
    const inpBus = document.getElementById('buscarInp');

    function enableFilters(enabled){
      [selTip, selEst, inpBus].forEach(el => { if (el) el.disabled = !enabled; });
    }

    let t;
    function debounceSubmit(){
      clearTimeout(t);
      t = setTimeout(()=>{ form.submit(); }, 400);
    }

    if (selEv){
      selEv.addEventListener('change', ()=>{
        // al cambiar evento, forzamos page=1
        const url = new URL(window.location.href);
        if (url.searchParams.has('page')) url.searchParams.delete('page');
        url.searchParams.set('evento', selEv.value);
        // limpiar texto si cambia evento (opcional)
        url.searchParams.delete('q');
        window.location.href = url.toString();
      });
      enableFilters(!!selEv.value);
    }

    if (selTip)  selTip.addEventListener('change', debounceSubmit);
    if (selEst)  selEst.addEventListener('change', debounceSubmit);
    if (inpBus)  inpBus.addEventListener('input', debounceSubmit);

    // Validación mínima de nota (visual)
    document.querySelectorAll('tr').forEach(tr=>{
      const minEl = tr.querySelector('td:nth-child(2) .text-muted b');
      const input = tr.querySelector('input[name="valor_numerico"]');
      if (minEl && input) {
        const minVal = parseFloat(minEl.textContent || 'NaN');
        input.addEventListener('input', ()=>{
          const v = parseFloat(input.value || 'NaN');
          if (!isNaN(minVal) && !isNaN(v) && v < minVal) input.classList.add('is-invalid');
          else input.classList.remove('is-invalid');
        });
      }
    });
  </script>
</body>
</html>
