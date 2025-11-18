<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  header("Location: ../index.php");
  exit();
}
require_once __DIR__ . '/../includes/conexion.php';

/* ====== Filtros ====== */
$eventId = isset($_GET['evento']) && $_GET['evento'] !== '' ? (int)$_GET['evento'] : null;
$estado  = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null; // Pendiente/Aprobado/Rechazado
$q       = trim($_GET['q'] ?? ''); // búsqueda por nombre o cédula

/* Combo de eventos */
$evRes = $conn->query("SELECT ID_EVE_CUR, TIT_EVE_CUR FROM EVENTOS_CURSOS ORDER BY FEC_INI_EVE_CUR DESC");
$estadosOpts = ['Pendiente', 'Aprobado', 'Rechazado'];

$rows = null;
$total = 0;
$page = 1;
$perPage = 50;

if ($eventId) {
  $where = ["i.ID_EVE_CUR = " . (int)$eventId];

  if ($estado) {
    $where[] = "p.ESTADO_VALIDACION = '" . $conn->real_escape_string($estado) . "'";
  }

  if ($q !== '') {
    $qLike = "%" . $conn->real_escape_string($q) . "%";
    $where[] = "(u.CED_USU LIKE '$qLike' OR CONCAT(u.APE_PRI_USU,' ',u.NOM_PRI_USU) LIKE '$qLike')";
  }

  $sqlWhere = "WHERE " . implode(" AND ", $where);

  $baseSql = "
    SELECT
      p.ID_PAG, p.ID_INS, p.FEC_PAG, p.MON_PAG, p.MET_PAG,
      p.URL_COMPROBANTE, p.ESTADO_VALIDACION, p.REVISADO_POR, p.REVISADO_EN,
      i.CED_USU, i.ID_EVE_CUR,
      u.NOM_PRI_USU, u.APE_PRI_USU,
      e.TIT_EVE_CUR
    FROM PAGOS p
    JOIN INSCRIPCIONES i ON i.ID_INS = p.ID_INS
    JOIN USUARIOS u      ON u.CED_USU = i.CED_USU
    JOIN EVENTOS_CURSOS e ON e.ID_EVE_CUR = i.ID_EVE_CUR
    $sqlWhere
  ";

  $perPage = 50;
  $page   = max(1, (int)($_GET['page'] ?? 1));
  $offset = ($page - 1) * $perPage;

  $total = (int)$conn->query("SELECT COUNT(*) c FROM ($baseSql) t")->fetch_assoc()['c'];
  $rows  = $conn->query($baseSql . " ORDER BY p.FEC_PAG DESC LIMIT $perPage OFFSET $offset");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verificación de Pagos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <style>
    /* Copia aquí el mismo CSS que estás usando en evidencias_por_evento.php */
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

    .card {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .table thead {
      background: var(--primary);
      color: white;
    }

    .table thead th {
      border: none;
      font-weight: 600;
      padding: 16px;
    }

    .table tbody td {
      padding: 14px;
      vertical-align: middle;
      border-color: #eee;
    }

    .table tbody tr:hover {
      background: var(--primary-light);
    }

    .avatar-circle {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: var(--primary-light);
      color: var(--primary);
      font-weight: bold;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 3px solid white;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

    .estado-toggle .btn.active {
      box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.08);
      transform: translateY(-1px);
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="../images/favico.png" alt="Logo UTA">
    </div>
    <a href="admin_inicio.php"><i class="fas fa-home me-2"></i> Inicio</a>
    <a href="gestionar_eventos.php"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
    <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> Gestionar Evidencias</a>
    <a href="verificar_pagos.php" class="active"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
    <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> Generación de Certificados</a>
    <a href="editar_usuario.php"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
    <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
    <a href="admin_configuraciones.php"><i class="fas fa-cog me-2"></i> Configuraciones</a>
    <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
  </div>

  <div class="content">
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="mb-1">Verificación de pagos</h3>
        <div class="text-muted">
          Selecciona un evento y revisa los comprobantes de pago para aprobar o rechazar.
        </div>
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
              <?php while ($ev = $evRes->fetch_assoc()): ?>
                <option value="<?= (int)$ev['ID_EVE_CUR'] ?>" <?= $eventId === (int)$ev['ID_EVE_CUR'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($ev['TIT_EVE_CUR']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="estado" id="estadoSel" class="form-select" <?= !$eventId ? 'disabled' : '' ?>>
              <option value="">Todos</option>
              <?php foreach ($estadosOpts as $opt): ?>
                <option value="<?= $opt ?>" <?= $estado === $opt ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" name="q" id="buscarInp" value="<?= htmlspecialchars($q) ?>" class="form-control"
              placeholder="Cédula o nombre" <?= !$eventId ? 'disabled' : '' ?>>
          </div>
        </form>
      </div>
    </div>

    <?php if (!$eventId): ?>
      <div class="alert alert-warning">
        <i class="fa fa-info-circle me-1"></i> Selecciona un <b>evento</b> para listar los pagos.
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-header">Resultados (<?= (int)$total ?>)</div>
        <div class="card-body table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Participante</th>
                <th>Fecha Pago</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Comprobante</th>
                <th>Estado</th>
                <th>Revisado</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($rows) while ($r = $rows->fetch_assoc()):
                $nombreCompleto = $r['APE_PRI_USU'] . ' ' . $r['NOM_PRI_USU'];
              ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar-circle"><?= strtoupper(substr($nombreCompleto, 0, 1)) ?></div>
                      <div>
                        <div class="fw-semibold"><?= htmlspecialchars($nombreCompleto) ?></div>
                        <div class="text-muted" style="font-size:.85rem"><?= $r['CED_USU'] ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($r['FEC_PAG']) ?></td>
                  <td>$ <?= number_format($r['MON_PAG'], 2) ?></td>
                  <td><?= htmlspecialchars($r['MET_PAG']) ?></td>

                  <!-- Comprobante -->
                  <td style="min-width:160px">
                    <?php if ($r['URL_COMPROBANTE']): ?>
                      <button type="button"
                        class="btn btn-sm btn-outline-secondary btn-ver-comp"
                        data-url="../uploads/comprobantes/<?= htmlspecialchars($r['URL_COMPROBANTE']) ?>"
                        data-nombre="<?= 'Pago #' . $r['ID_PAG'] . ' - ' . $nombreCompleto ?>">
                        <i class="fa fa-file"></i> Ver comprobante
                      </button>
                    <?php else: ?>
                      <span class="text-muted">Sin archivo</span>
                    <?php endif; ?>
                  </td>

                  <!-- Estado con iconos (auto-submit) -->
                  <td style="min-width:150px">
                    <form class="form-pago d-inline" action="pago_update.php" method="post">
                      <input type="hidden" name="id_pag" value="<?= (int)$r['ID_PAG'] ?>">
                      <input type="hidden" name="id_ins" value="<?= (int)$r['ID_INS'] ?>">
                      <input type="hidden" name="id_evento" value="<?= (int)$r['ID_EVE_CUR'] ?>">

                      <!-- aquí guardamos el estado real que se enviará al PHP -->
                      <input type="hidden" name="estado" value="<?= htmlspecialchars($r['ESTADO_VALIDACION']) ?>">

                      <div class="btn-group estado-toggle" role="group" aria-label="Estado pago">
                        <!-- Pendiente -->
                        <button type="button"
                          class="btn btn-sm btn-outline-secondary btn-estado-pago <?= $r['ESTADO_VALIDACION'] === 'Pendiente' ? 'active' : '' ?>"
                          data-estado="Pendiente"
                          title="Pendiente">
                          <i class="fa fa-clock"></i>
                        </button>
                        <!-- Aprobado -->
                        <button type="button"
                          class="btn btn-sm btn-outline-success btn-estado-pago <?= $r['ESTADO_VALIDACION'] === 'Aprobado' ? 'active' : '' ?>"
                          data-estado="Aprobado"
                          title="Aprobado">
                          <i class="fa fa-check"></i>
                        </button>
                        <!-- Rechazado -->
                        <button type="button"
                          class="btn btn-sm btn-outline-danger btn-estado-pago <?= $r['ESTADO_VALIDACION'] === 'Rechazado' ? 'active' : '' ?>"
                          data-estado="Rechazado"
                          title="Rechazado">
                          <i class="fa fa-times"></i>
                        </button>
                      </div>

                      <!-- Texto pequeño con el estado actual (opcional) -->
                      <div class="mt-1 text-muted" style="font-size:.75rem;">
                        <?= htmlspecialchars($r['ESTADO_VALIDACION']) ?>
                      </div>
                    </form>
                  </td>

                  <!-- Revisado -->
                  <td style="min-width:170px; font-size:.85rem;">
                    <?php if ($r['REVISADO_POR']): ?>
                      <div><b>Por:</b> <?= htmlspecialchars($r['REVISADO_POR']) ?></div>
                    <?php endif; ?>
                    <?php if ($r['REVISADO_EN']): ?>
                      <div><b>En:</b> <?= htmlspecialchars($r['REVISADO_EN']) ?></div>
                    <?php endif; ?>
                    <?php if (!$r['REVISADO_POR'] && !$r['REVISADO_EN']): ?>
                      <span class="text-muted">Sin revisar</span>
                    <?php endif; ?>
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
              for ($p = 1; $p <= $pages; $p++) {
                $qs = $_GET;
                $qs['page'] = $p;
                $href = '?' . http_build_query($qs);
                echo '<li class="page-item ' . ($p === $page ? 'active' : '') . '"><a class="page-link" href="' . $href . '">' . $p . '</a></li>';
              }
              echo '</ul></nav>';
            endif;
          }
          ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Modal para ver comprobante -->
  <div class="modal fade" id="compModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="compModalLabel">Comprobante de pago</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-0">
          <iframe id="compFrame" src="" style="border:0; width:100%; height:80vh;"></iframe>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const form = document.getElementById('filtros');
    const selEv = document.getElementById('eventoSel');
    const selEst = document.getElementById('estadoSel');
    const inpBus = document.getElementById('buscarInp');

    function enableFilters(enabled) {
      [selEst, inpBus].forEach(el => {
        if (el) el.disabled = !enabled;
      });
    }

    let t;

    function debounceSubmit() {
      clearTimeout(t);
      t = setTimeout(() => {
        form.submit();
      }, 400);
    }

    if (selEv) {
      selEv.addEventListener('change', () => {
        const url = new URL(window.location.href);
        if (url.searchParams.has('page')) url.searchParams.delete('page');
        url.searchParams.set('evento', selEv.value);
        url.searchParams.delete('q');
        window.location.href = url.toString();
      });
      enableFilters(!!selEv.value);
    }

    if (selEst) selEst.addEventListener('change', debounceSubmit);
    if (inpBus) inpBus.addEventListener('input', debounceSubmit);

    // Modal comprobante
    const compModalEl = document.getElementById('compModal');
    const compFrame = document.getElementById('compFrame');
    const compTitleEl = document.getElementById('compModalLabel');

    if (compModalEl && compFrame && compTitleEl && window.bootstrap) {
      const compModal = new bootstrap.Modal(compModalEl);

      document.querySelectorAll('.btn-ver-comp').forEach(btn => {
        btn.addEventListener('click', e => {
          e.preventDefault();
          const url = btn.dataset.url;
          const nombre = btn.dataset.nombre || 'Comprobante de pago';

          compTitleEl.textContent = nombre;
          compFrame.src = url;
          compModal.show();
        });
      });

      compModalEl.addEventListener('hidden.bs.modal', () => {
        compFrame.src = '';
      });
    }

    // ===== Toggle de estado con iconos en pagos =====
    document.querySelectorAll('.btn-estado-pago').forEach(btn => {
      btn.addEventListener('click', () => {
        const estado = btn.dataset.estado;
        const form = btn.closest('form');
        const hiddenEstado = form.querySelector('input[name="estado"]');

        // Setear el valor que se mandará al PHP
        hiddenEstado.value = estado;

        // Marcar visualmente el activo
        form.querySelectorAll('.btn-estado-pago').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Enviar el formulario automáticamente
        form.submit();
      });
    });
  </script>
</body>

</html>