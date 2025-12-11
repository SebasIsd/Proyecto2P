<?php
// mis_eventos.php
session_start();
if (!isset($_SESSION['correo']) || !isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}
require_once __DIR__ . '/../includes/conexion.php';

$cedula = $_SESSION['cedula'];

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
<title>Mis eventos</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<style>
<?php /* 🔥 Aquí NO se tocó nada */ ?>
:root { --primary:#a30000; --primary-hover:#d51313; --primary-light:#ffebeb; --gray-light:#f8f9fa; --gray:#6c757d; --dark:#333; --shadow:0 8px 25px rgba(0,0,0,0.12); --radius:16px; }
body { font-family:'Segoe UI'; background:linear-gradient(135deg,#f5f5f5 0%,#e0e0e0 100%); color:var(--dark); min-height:100vh; }
.sidebar { position:fixed; top:0; left:0; width:260px; height:100vh; background:var(--primary); color:white; padding:25px 0; box-shadow:5px 0 20px rgba(0,0,0,0.15); z-index:1000; }
.sidebar .logo { text-align:center; margin-bottom:40px; padding:0 25px; }
.sidebar .logo img { width:130px; border-radius:50%; border:5px solid rgba(255,255,255,0.25); }
.sidebar a { color:rgba(255,255,255,0.9); padding:16px 28px; display:flex; align-items:center; text-decoration:none; font-weight:500; transition:all .3s; border-left:4px solid transparent; }
.sidebar a:hover, .sidebar a.active { background:var(--primary-hover); border-left-color:white; padding-left:32px; }
.content { margin-left:260px; padding:32px; }
.card { border-radius:var(--radius); box-shadow:var(--shadow); border:none; overflow:hidden; }
.card-header { background:var(--primary); color:white; font-weight:600; }
.carousel-img { height:300px; object-fit:cover; width:100%; border-bottom:4px solid rgba(0,0,0,0.03);}
.btn-uta { background:linear-gradient(90deg,#a30000,#a02727); color:#fff; border:none; border-radius:8px;}
.btn-uta:hover { background:var(--primary-hover);}
</style>
</head>
<body>

   <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="admin_inicio.php" class="active"><i class="fas fa-home me-2"></i> Inicio</a>
        <a href="miseventos.php"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
        <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> Requisitos de Inscripción</a>
        <a href="requisitosAprobacion.php"><i class="fa fa-clipboard-check"></i> Requisitos de Aprobación</a>
        <a href="verificar_pagos.php"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
        <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> Generación de Certificados</a>
        <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
    </div>

<div class="content">
<h3 class="mb-4">Mis eventos</h3>

<?php if (count($eventos) === 0): ?>
<div class="card p-4"><div class="alert alert-info mb-0">No hay eventos asignados.</div></div>
<?php else: ?>

<!-- 🖼 SLIDER -->
<div id="misEventosCarousel" class="carousel slide mb-4 card" data-bs-ride="carousel">
  <div class="carousel-inner">
    <?php foreach($eventos as $i => $ev):
      $img = !empty($ev['IMG_EVE_CUR']) ? '../' . htmlspecialchars($ev['IMG_EVE_CUR']) : 'https://via.placeholder.com/1200x600?text=Evento+' . urlencode($ev['TIT_EVE_CUR']);
    ?>
    <div class="carousel-item <?= $i===0?'active':'' ?>">
      <img src="<?= $img ?>" class="carousel-img">
      <div class="carousel-caption text-start" style="bottom:18px;left:18px;">
        <div style="background:#fff;padding:10px;border-radius:10px;">
          <h5><?= htmlspecialchars($ev['TIT_EVE_CUR']) ?></h5>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- 📌 TABLA corregida -->
<div class="card">
  <div class="card-header">Listado de eventos</div>
  <div class="card-body table-responsive">
    <table class="table align-middle">
      <tbody>
        <?php foreach($eventos as $ev): ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($ev['TIT_EVE_CUR']) ?></strong><br>
            <small class="text-muted">
              <?= htmlspecialchars(
                    mb_strimwidth(
                        $ev['DES_EVE_CUR'] ?? "Sin descripción",
                        0,
                        140,
                        "..."
                    )
                 ) ?>
            </small>
          </td>

          <td style="min-width:170px;">
            <?php
              $ini = $ev['FEC_INI_EVE_CUR'] ? strtotime($ev['FEC_INI_EVE_CUR']) : false;
              $fin = $ev['FEC_FIN_EVE_CUR'] ? strtotime($ev['FEC_FIN_EVE_CUR']) : false;
              echo $ini ? date('d/m/Y', $ini) : '—';
              echo " — ";
              echo $fin ? date('d/m/Y', $fin) : '—';
            ?>
          </td>

          <td><?= (int)$ev['CUPOS_DISPONIBLES'] ?> / <?= (int)$ev['CAPACIDAD_MAXIMA'] ?></td>

          <td class="text-end">
            <a href="editarEvento.php?id=<?= (int)$ev['ID_EVE_CUR'] ?>" class="btn btn-sm btn-warning">Editar</a>
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
</body>
</html>
