<?php
// mis_eventos.php
session_start();
if (!isset($_SESSION['correo']) || !isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}
require_once __DIR__ . '/../includes/conexion.php';

$cedula = $_SESSION['cedula'];

// Traer eventos donde el usuario es responsable o ponente
$sql = "
  SELECT e.ID_EVE_CUR, e.TIT_EVE_CUR, e.DES_EVE_CUR, e.FEC_INI_EVE_CUR, e.FEC_FIN_EVE_CUR,
         e.IMG_EVE_CUR, e.CUPOS_DISPONIBLES, e.CAPACIDAD_MAXIMA
  FROM EVENTOS_CURSOS e
  JOIN PERSONAL_EVENTO p ON e.ID_EVE_CUR = p.ID_EVE_CUR
  WHERE p.CED_USU = ? AND (p.ES_RESPONSABLE = 1 OR UPPER(p.ROL_EVENTO) = 'PONENTE')
  GROUP BY e.ID_EVE_CUR
  ORDER BY e.FEC_INI_EVE_CUR DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$res = $stmt->get_result();
$eventos = [];
while ($r = $res->fetch_assoc()) $eventos[] = $r;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Mis eventos — Panel Responsable</title>

  <!-- Bootstrap + FontAwesome (igual que tus páginas) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

  <style>
    /* Copié y adapté el estilo que me pasaste para consistencia */
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
      position: fixed; top:0; left:0; width:260px; height:100vh;
      background: var(--primary); color:white; padding:25px 0; box-shadow:5px 0 20px rgba(0,0,0,0.15); z-index:1000;
    }
    .sidebar .logo { text-align:center; margin-bottom:40px; padding:0 25px; }
    .sidebar .logo img { width:130px; border-radius:50%; border:5px solid rgba(255,255,255,0.25); }
    .sidebar a { color:rgba(255,255,255,0.9); padding:16px 28px; display:flex; align-items:center; text-decoration:none; font-weight:500; transition:all .3s; border-left:4px solid transparent; }
    .sidebar a i { width:24px; margin-right:14px; font-size:1.15rem; }
    .sidebar a:hover, .sidebar a.active { background:var(--primary-hover); color:white; border-left-color:white; padding-left:32px; }

    .content { margin-left:260px; padding:32px; }
    .card { border-radius:var(--radius); box-shadow:var(--shadow); border: none; overflow:hidden; }
    .card-header { background: var(--primary); color: white; font-weight:600; }

    .carousel-img {
      height: 300px;
      object-fit: cover;
      width:100%;
      border-bottom: 4px solid rgba(0,0,0,0.03);
    }

    .event-title { font-weight:600; color:#2b2b2b; }
    .btn-uta { background: linear-gradient(90deg,#a30000,#a02727); color:#fff; border:none; border-radius:8px; }
    .btn-uta:hover { background: var(--primary-hover); }

    @media (max-width:768px) {
      .sidebar { width:80px; } .content { margin-left:80px; padding:18px; }
      .sidebar .logo img { width:50px; } .sidebar a span { display:none; }
    }
  </style>
</head>
<body>
  <!-- Sidebar (igual al resto de tu app) -->
  <div class="sidebar">
    <div class="logo"><img src="../images/favico.png" alt="Logo UTA"></div>
    <a href="admin_inicio.php"><i class="fas fa-home me-2"></i> Inicio</a>
    <a href="gestionar_eventos.php"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
    <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> Gestionar Evidencias</a>
    <a href="verificar_pagos.php"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
    <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> Generación de Certificados</a>
    <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
    <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
  </div>

  <div class="content">
    <div class="mb-4">
      <div class="d-flex align-items-center justify-content-between gap-3">
        <div>
          <h3 class="mb-0">Mis eventos</h3>
          <small class="text-muted">Eventos en los que eres Responsable o Ponente (cédula: <strong><?= htmlspecialchars($cedula) ?></strong>)</small>
        </div>
        <div>
          <a href="crear_evento.php" class="btn btn-uta"><i class="fa fa-plus me-2"></i> Crear evento</a>
        </div>
      </div>
    </div>

    <?php if (count($eventos) === 0): ?>
      <div class="card p-4">
        <div class="alert alert-info mb-0">No apareces como responsable/ponente en ningún evento aún.</div>
      </div>
    <?php else: ?>

      <!-- SLIDER con imágenes de tus eventos -->
      <div id="misEventosCarousel" class="carousel slide mb-4 card" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php foreach($eventos as $i => $ev): 
            $img = !empty($ev['IMG_EVE_CUR']) ? '../' . $ev['IMG_EVE_CUR'] : 'https://via.placeholder.com/1200x600?text=Evento+' . urlencode($ev['TIT_EVE_CUR']);
            $active = $i === 0 ? 'active' : '';
          ?>
            <div class="carousel-item <?= $active ?>">
              <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100 carousel-img" alt="<?= htmlspecialchars($ev['TIT_EVE_CUR']) ?>">
              <div class="carousel-caption d-none d-md-block text-start" style="bottom:18px; left:18px; right:18px; text-shadow: none;">
                <div style="background: rgba(255,255,255,0.92); padding:12px; border-radius:10px; color:#222;">
                  <h5 class="mb-1 event-title"><?= htmlspecialchars($ev['TIT_EVE_CUR']) ?></h5>
                  <div class="small text-muted">
                    <?= date('d/m/Y', strtotime($ev['FEC_INI_EVE_CUR'])) ?> — <?= date('d/m/Y', strtotime($ev['FEC_FIN_EVE_CUR'])) ?>
                    &nbsp;•&nbsp; <?= (int)$ev['CUPOS_DISPONIBLES'] ?> / <?= (int)$ev['CAPACIDAD_MAXIMA'] ?> cupos
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#misEventosCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#misEventosCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
        </button>
      </div>

      <!-- Tabla con tus eventos y acción: Añadir información -->
      <div class="card">
        <div class="card-header">Listado de eventos (<?= count($eventos) ?>)</div>
        <div class="card-body table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Evento</th>
                <th>Fechas</th>
                <th>Cupos</th>
                <th class="text-end">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($eventos as $ev): ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($ev['TIT_EVE_CUR']) ?></div>
                    <div class="text-muted" style="font-size:.85rem"><?= htmlspecialchars(mb_strimwidth($ev['DES_EVE_CUR'], 0, 140, '...')) ?></div>
                  </td>
                  <td style="min-width:170px;">
                    <?= date('d/m/Y', strtotime($ev['FEC_INI_EVE_CUR'])) ?> — <?= date('d/m/Y', strtotime($ev['FEC_FIN_EVE_CUR'])) ?>
                  </td>
                  <td style="min-width:120px;">
                    <?= (int)$ev['CUPOS_DISPONIBLES'] ?> / <?= (int)$ev['CAPACIDAD_MAXIMA'] ?>
                  </td>
                 <td class="text-end">

    <!-- Botón EDITAR EVENTO -->
    <a href="editar_evento.php?id=<?= (int)$ev['ID_EVE_CUR'] ?>" 
       class="btn btn-sm btn-warning me-2"
       style="border-radius:8px; font-weight:600;">
        <i class="fa fa-pen-to-square me-1"></i> Editar
    </a>

    <!-- Botón AGREGAR INFORMACIÓN -->
    <a href="gestionar_eventos.php?evento=<?= (int)$ev['ID_EVE_CUR'] ?>" 
       class="btn btn-sm btn-uta"
       style="font-weight:600;">
        <i class="fa fa-plus-circle me-1"></i> Agregar
    </a>

</td>

                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php endif; ?>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Inicia el carousel automáticamente si hay más de 1 item
    const carouselEl = document.querySelector('#misEventosCarousel');
    if (carouselEl) {
      const items = carouselEl.querySelectorAll('.carousel-item').length;
      if (items <= 1) {
        // si solo hay 1 elemento, ocultar controles
        carouselEl.querySelectorAll('.carousel-control-prev, .carousel-control-next').forEach(el => el.style.display = 'none');
      }
    }
  </script>
</body>
</html>
