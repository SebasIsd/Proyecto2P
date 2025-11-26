<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  header("Location: ../index.php"); exit();
}
require_once __DIR__ . '/../includes/conexion.php';

$idEvento = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
if ($idEvento <= 0) {
    die('Evento inválido.');
}

// Obtener info del evento
$sqlEv = "
  SELECT e.*, te.NOM_TIPO_EVE
  FROM EVENTOS_CURSOS e
  JOIN TIPOS_EVENTO te ON te.ID_TIPO_EVE = e.ID_TIPO_EVE
  WHERE e.ID_EVE_CUR = ?
";
$stmt = $conn->prepare($sqlEv);
$stmt->bind_param("i", $idEvento);
$stmt->execute();
$evRes = $stmt->get_result();
$evento = $evRes->fetch_assoc();
$stmt->close();

if (!$evento) {
    die('Evento no encontrado.');
}

// Obtener inscripciones aptas
$sqlAptos = "
SELECT
  i.ID_INS,
  i.CED_USU,
  i.CERT_GENERADO,
  i.RUTA_CERTIFICADO,
  u.NOM_PRI_USU,
  u.NOM_SEG_USU,
  u.APE_PRI_USU,
  u.APE_SEG_USU,
  i.ESTADO_INS
FROM INSCRIPCIONES i
JOIN USUARIOS u       ON u.CED_USU = i.CED_USU
JOIN EVENTOS_CURSOS e ON e.ID_EVE_CUR = i.ID_EVE_CUR
WHERE i.ID_EVE_CUR = ?
  AND e.FEC_FIN_EVE_CUR <= CURDATE()
  AND i.ESTADO_INS = 'Completado'
  AND (
    e.MOD_EVE_CUR = 'Gratis'
    OR EXISTS (
      SELECT 1
      FROM PAGOS p
      WHERE p.ID_INS = i.ID_INS
        AND p.ESTADO_VALIDACION = 'Aprobado'
    )
  )
  AND NOT EXISTS (
    SELECT 1
    FROM EVENTOS_REQUISITOS er
    LEFT JOIN EVIDENCIAS ev
      ON ev.ID_REQ = er.ID_REQ
     AND ev.ID_INS = i.ID_INS
    WHERE er.ID_EVE_CUR = i.ID_EVE_CUR
      AND er.OBLIGATORIO = 1
      AND (
        ev.ID_EVIDENCIA IS NULL
        OR ev.ESTADO_VALIDACION <> 'Aprobado'
        OR (
          er.VALOR_MINIMO_APROBATORIO IS NOT NULL
          AND (ev.VALOR_NUMERICO IS NULL OR ev.VALOR_NUMERICO < er.VALOR_MINIMO_APROBATORIO)
        )
      )
  )
ORDER BY u.APE_PRI_USU, u.NOM_PRI_USU
";
$stmt = $conn->prepare($sqlAptos);
$stmt->bind_param("i", $idEvento);
$stmt->execute();
$aptosRes = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Participantes aptos para certificado</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    :root {
      --primary: #a30000;
      --primary-hover: #d51313;
      --primary-light: #ffebeb;
      --gray-light: #f8f9fa;
      --gray: #6c757d;
      --dark: #333;
      --shadow: 0 8px 25px rgba(0,0,0,0.12);
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
    .card {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      margin-bottom: 20px;
    }
    .card-header {
      background: var(--primary);
      color: white;
      font-weight: 600;
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
    .badge-soft {
      background: var(--primary-light);
      color: var(--primary);
      border-radius: 999px;
      padding: 4px 10px;
      font-size: 0.8rem;
    }
    .search-input {
      max-width: 420px;
      width: 100%;
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
  <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="admin_inicio.php"><i class="fas fa-home me-2"></i> Inicio</a>
        <a href="gestionar_eventos.php"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
        <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> Gestionar Evidencias</a>
        <a href="verificar_pagos.php"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
        <a href="eventos_certificables.php" class="active"><i class="fa fa-certificate"></i> Generación de Certificados</a>
        <a href="editar_usuario.php"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
        <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
        <a href="#"><i class="fas fa-cog me-2"></i> Configuraciones</a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
    </div>
  <!-- Contenido principal -->
  <div class="content">
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
          <h3 class="mb-1">Participantes aptos para certificado</h3>
          <p class="text-muted mb-1">
            Evento: <strong><?= htmlspecialchars($evento['TIT_EVE_CUR']) ?></strong>
          </p>
          <p class="text-muted mb-0">
            Fechas:
            <?= date('d/m/Y', strtotime($evento['FEC_INI_EVE_CUR'])) ?>
            -
            <?= date('d/m/Y', strtotime($evento['FEC_FIN_EVE_CUR'])) ?>
            |
            Tipo: <?= htmlspecialchars($evento['NOM_TIPO_EVE']) ?> |
            Modalidad: <?= htmlspecialchars($evento['MOD_EVE_CUR']) ?>
          </p>
        </div>

      
      </div>
    </div>

     <!-- Input de búsqueda en tiempo real -->
        <div style="min-width:260px; max-width:420px; width:100%;">
          <input id="buscarParticipante" class="form-control search-input" type="search"
                 placeholder="Buscar por cédula, nombre o estado..." aria-label="Buscar participantes">
        </div></br>

    <div class="card">
      <div class="card-header">
        Participantes aptos (<?= $aptosRes->num_rows ?>)
      </div>
      <div class="card-body table-responsive">
        <table id="tablaParticipantes" class="table align-middle">
          <thead>
            <tr>
              <th>Cédula</th>
              <th>Participante</th>
              <th>Estado inscripción</th>
              <th class="text-center">Certificado</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($aptosRes->num_rows === 0): ?>
              <tr><td colspan="4" class="text-center text-muted">No hay participantes aptos.</td></tr>
            <?php else: ?>
              <?php while($r = $aptosRes->fetch_assoc()):
                $nombreCompleto = trim(
                  $r['APE_PRI_USU'].' '.
                  $r['APE_SEG_USU'].' '.
                  $r['NOM_PRI_USU'].' '.
                  $r['NOM_SEG_USU']
                );
                $yaTieneCert = !empty($r['CERT_GENERADO']) && !empty($r['RUTA_CERTIFICADO']);
              ?>
                <tr data-ins-id="<?= (int)$r['ID_INS'] ?>">
                  <td class="cedula"><?= htmlspecialchars($r['CED_USU']) ?></td>
                  <td class="nombre"><?= htmlspecialchars($nombreCompleto) ?></td>
                  <td class="estado">
                    <span class="badge-soft">
                      <?= htmlspecialchars($r['ESTADO_INS']) ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <button type="button"
                            class="btn btn-sm <?= $yaTieneCert ? 'btn-outline-primary' : 'btn-success' ?> btn-cert"
                            data-id-ins="<?= (int)$r['ID_INS'] ?>">
                      <i class="fa fa-file-pdf"></i>
                      <?= $yaTieneCert ? 'Ver certificado' : 'Generar certificado' ?>
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
              <!-- fila de "no results" oculta por defecto -->
              <tr id="noResults" style="display:none;">
                <td colspan="4" class="text-center text-muted py-4">No se encontraron participantes.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal para ver/generar certificado -->
  <div class="modal fade" id="certModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Certificado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-0">
          <iframe id="certFrame" src="" style="border:0; width:100%; height:80vh;"></iframe>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Manejo del modal de certificado
    const certModalEl = document.getElementById('certModal');
    const certFrame   = document.getElementById('certFrame');

    if (certModalEl && certFrame && window.bootstrap) {
      const certModal = new bootstrap.Modal(certModalEl);

      document.querySelectorAll('.btn-cert').forEach(btn => {
        btn.addEventListener('click', () => {
          const idIns = btn.dataset.idIns;
          const url   = '../certificados/certificado.php?id_ins=' + encodeURIComponent(idIns);
          certFrame.src = url;
          certModal.show();
        });
      });

      certModalEl.addEventListener('hidden.bs.modal', () => {
        // limpiar src para liberar recursos al cerrar
        certFrame.src = '';
      });
    }

    // BÚSQUEDA EN TIEMPO REAL (sin botón) - debounce 200ms
    (function() {
      const input = document.getElementById('buscarParticipante');
      const tabla = document.getElementById('tablaParticipantes');
      if (!input || !tabla) return;
      const tbody = tabla.querySelector('tbody');
      const noResults = document.getElementById('noResults');

      function debounce(fn, delay) {
        let t;
        return function(...args) {
          clearTimeout(t);
          t = setTimeout(() => fn.apply(this, args), delay);
        };
      }

      function normalize(text) {
        return (text || '').toString().trim().toLowerCase();
      }

      function filtrar(q) {
        q = normalize(q);
        // todas las filas reales (excluimos la fila noResults si existe)
        const filas = Array.from(tbody.querySelectorAll('tr')).filter(tr => tr.id !== 'noResults');
        let anyVisible = false;

        filas.forEach(tr => {
          const cedula = normalize(tr.querySelector('.cedula')?.textContent);
          const nombre = normalize(tr.querySelector('.nombre')?.textContent);
          const estado = normalize(tr.querySelector('.estado')?.textContent);
          const combined = `${cedula} ${nombre} ${estado}`.replace(/\s+/g, ' ');

          if (!q || combined.indexOf(q) !== -1) {
            tr.style.display = '';
            anyVisible = true;
          } else {
            tr.style.display = 'none';
          }
        });

        if (!anyVisible) {
          if (noResults) noResults.style.display = '';
        } else {
          if (noResults) noResults.style.display = 'none';
        }
      }

      const debounced = debounce((e) => filtrar(e.target.value), 200);
      input.addEventListener('input', debounced);
    })();
  </script>
</body>
</html>
