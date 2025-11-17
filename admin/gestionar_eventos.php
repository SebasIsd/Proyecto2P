<?php
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

// Cargar lista de eventos
$eventos = $conn->query("
    SELECT e.ID_EVE_CUR, e.TIT_EVE_CUR, e.FEC_INI_EVE_CUR, t.NOM_TIPO_EVE, e.ACTIVO
    FROM EVENTOS_CURSOS e
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    ORDER BY e.FEC_INI_EVE_CUR DESC
")->fetch_all(MYSQLI_ASSOC);

// Cargar catálogos para modal de edición (si necesitas editar)
//$tipos = $conn->query("SELECT ID_TIPO_EVE, NOM_TIPO_EVE FROM TIPOS_EVENTO ORDER BY NOM_TIPO_EVE")->fetch_all(MYSQLI_ASSOC);
//$requisitos = $conn->query("SELECT ID_REQ, NOM_REQ FROM REQUISITOS ORDER BY NOM_REQ")->fetch_all(MYSQLI_ASSOC);
//$carreras = $conn->query("SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA")->fetch_all(MYSQLI_ASSOC);
// Cargar lista de eventos (ya lo tienes)
// Favoritos globales marcados por el admin
$favs = $conn->query("SELECT ID_EVE_CUR FROM EVENTOS_FAVORITOS")->fetch_all(MYSQLI_ASSOC);
$favSet = [];
foreach ($favs as $f) { $favSet[(int)$f['ID_EVE_CUR']] = true; }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Eventos - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
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

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(163,0,0,0.15);
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
                <a href="gestion_pagos.php"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
        <a href="editar_usuario.php"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
        <a href="#"><i class="fas fa-chart-bar me-2"></i> Estadísticas</a>
        <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
        <a href="#"><i class="fas fa-cog me-2"></i> Configuraciones</a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido -->
    <div class="content">
  <div class="page-header">
    <i class="fas fa-calendar-check"></i>
    <h1>Gestionar Eventos</h1>
    <a href="n.php" class="btn-primary">
      <i class="fas fa-plus"></i> Nuevo Evento
    </a>
  </div>

  <!-- ✅ Alertas dentro de .content -->
  <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      El evento se actualizó correctamente.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php elseif (isset($_GET['err'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      Error: <?= htmlspecialchars($_GET['err']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header-custom">
      <i class="fas fa-list me-2"></i> Lista de Eventos
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Fav</th>
              <th>Título</th>
              <th>Fecha Inicio</th>
              <th>Tipo</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
<tbody>
<?php if (empty($eventos)): ?>
  <tr><td colspan="6" class="text-center py-4 text-muted">No hay eventos registrados aún.</td></tr>
<?php else: foreach ($eventos as $e):
  $id = (int)$e['ID_EVE_CUR'];
  $isFav = isset($favSet[$id]);
?>
  <tr data-evento-row="<?= $id ?>">
    <td class="text-center">
      <button type="button"
              class="btn btn-link p-0 btn-fav"
              aria-label="Marcar favorito"
              data-id="<?= $id ?>"
              data-fav="<?= $isFav ? '1':'0' ?>">
        <!-- fas = lleno, far = contorno -->
        <i class="fa<?= $isFav ? 's':'r' ?> fa-heart fav-icon<?= $isFav ? ' text-danger':'' ?>"></i>
      </button>
    </td>
    <td><strong><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></strong></td>
    <td><?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?></td>
    <td><?= htmlspecialchars($e['NOM_TIPO_EVE']) ?></td>
    <td>
      <?php if ((int)$e['ACTIVO'] === 1): ?>
        <span class="badge bg-success">Activo</span>
      <?php else: ?>
        <span class="badge bg-danger">Inactivo</span>
      <?php endif; ?>
    </td>
    <td>
      <button type="button"
              class="btn-edit"
              onclick="location.href='editarEvento.php?id=<?= $id ?>'">
        <i class="fas fa-edit"></i> Editar
      </button>
      <button type="button" class="btn-delete" onclick="eliminarEvento(<?= $id ?>)">
        <i class="fas fa-trash"></i> Eliminar
      </button>
    </td>
  </tr>
<?php endforeach; endif; ?>
</tbody>
        </table>
      </div>
    </div>
  </div>
</div> 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   <script>
async function eliminarEvento(id){
  if(!confirm('¿Seguro que deseas eliminar el evento?')) return;
  const row = document.querySelector(`[data-evento-row="${id}"]`);
  const btns = row ? row.querySelectorAll('button') : [];
  btns.forEach(b => b.disabled = true);

  try{
    const res = await fetch('eliminar_evento.php', {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: new URLSearchParams({ id: String(id) })
    });
    const ct = res.headers.get('content-type') || '';
    const json = ct.includes('application/json') ? await res.json() : { success:false, message: await res.text() };

    if(json.success){
      if(row) row.remove();
      alert('Evento eliminado con éxito.');
    }else{
      alert('No se pudo eliminar: ' + (json.message || 'Error'));
      btns.forEach(b => b.disabled = false);
    }
  }catch(e){
    console.error(e);
    alert('Error de red al eliminar.');
    btns.forEach(b => b.disabled = false);
  }
}

// Manejar click en botones de favorito
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.btn-fav');
  if (!btn) return;

  const id = btn.getAttribute('data-id');
  const icon = btn.querySelector('.fav-icon');
  btn.disabled = true;

  try {
    const res = await fetch('toggle_favorito.php', {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: new URLSearchParams({ id })
    });
    const json = await res.json();

    if (!json.success) {
      alert(json.message || 'No se pudo actualizar el favorito');
      btn.disabled = false;
      return;
    }

    if (json.favorito) {
      icon.classList.remove('far');
      icon.classList.add('fas', 'text-danger');
      btn.setAttribute('data-fav', '1');
    } else {
      icon.classList.remove('fas', 'text-danger');
      icon.classList.add('far');
      btn.setAttribute('data-fav', '0');
    }
  } catch (err) {
    console.error(err);
    alert('Error de red al marcar favorito');
  } finally {
    btn.disabled = false;
  }
});

</script>

</body>
</html>