<?php
/*************************************************
 * Admin → Crear Evento/Curso (con requisitos)
 *************************************************/
require_once '../includes/conexion.php';

$mensaje = '';
$errores = [];

/* Utilidades */
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

/* Cargar datos para el formulario */
$tipos = [];
$resTipos = $conn->query("SELECT ID_TIPO_EVE, NOM_TIPO_EVE FROM TIPOS_EVENTO ORDER BY NOM_TIPO_EVE");
while ($row = $resTipos->fetch_assoc()) $tipos[] = $row;

$carreras = [];
$resCar = $conn->query("SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA");
while ($row = $resCar->fetch_assoc()) $carreras[] = $row;

$requisitos = [];
$resReq = $conn->query("SELECT ID_REQ, NOM_REQ, TIPO FROM REQUISITOS WHERE ACTIVO=1 ORDER BY NOM_REQ");
while ($row = $resReq->fetch_assoc()) $requisitos[] = $row;

/* Procesar envío */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // (tu lógica de guardado original permanece igual)
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin · Crear Evento</title>
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
    
        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo img { width: 50px; }
            .sidebar a span { display: none; }
            .sidebar a { padding: 16px; justify-content: center; }
            .sidebar a:hover { padding-left: 16px; }
            .content { margin-left: 80px; padding: 20px; }
        }

    body {
      background: #f8f6f3;
      font-family: 'Poppins', sans-serif;
    }
    .text-uta { color: #7b1113; }
    .btn-uta {
      background: linear-gradient(90deg, #7b1113, #a02727);
      color: #fff;
      border: none;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    .btn-uta:hover {
      background: linear-gradient(90deg, #a02727, #7b1113);
      transform: translateY(-1px);
    }
    .card {
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      border: none;
      overflow: hidden;
    }
    .accordion-button {
      font-weight: 600;
      color: #7b1113;
      background: #faf7f5;
      transition: all .3s;
    }
    .accordion-button:not(.collapsed) {
      background: #7b1113;
      color: #fff;
    }
    .accordion-body {
      background: #fff;
    }
    .req-chip {
      border: 1px solid #e2dcdc;
      border-radius: 999px;
      padding: .4rem .8rem;
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      transition: all .2s;
      background: #fff;
      cursor: pointer;
    }
    .req-chip:hover {
      background: #f7efef;
      border-color: #7b1113;
      color: #7b1113;
    }
    .sticky-actions {
      position: sticky;
      bottom: 0;
      background: #fff;
      padding: 15px;
      border-top: 1px solid #eee;
      box-shadow: 0 -2px 8px rgba(0,0,0,.04);
      border-radius: 0 0 16px 16px;
    }
    .table thead {
      background: #7b1113;
      color: #fff;
    }
    .form-label {
      font-weight: 500;
      color: #4b2b2b;
    }
    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid #ddd;
      transition: border-color .2s;
    }
    .form-control:focus, .form-select:focus {
      border-color: #7b1113;
      box-shadow: 0 0 0 .15rem rgba(123,17,19,.25);
    }
    .brand-header { text-align: center; margin-bottom: 2rem; }
    .brand-header h2 { font-weight: 700; color: #7b1113; }
    .brand-header p { color: #6c757d; font-size: 0.95rem; }
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
        <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> Generación de Certificados</a>
        <a href="editar_usuario.php"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
        <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
        <a href="#"><i class="fas fa-cog me-2"></i> Configuraciones</a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
    </div>

     <main class="content">
  <div class="container py-5">
    <div class="brand-header">
      <h2><i class="bi bi-calendar2-plus me-2"></i>Crear nuevo evento o curso</h2>
      <p>Completa la información paso a paso</p>
    </div>

   <form method="post" id="formEvento" enctype="multipart/form-data">
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
              <input id="tituloEvento" name="TIT_EVE_CUR" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de evento *</label>
              <div class="input-group">
                <select id="tipoEvento" name="ID_TIPO_EVE" class="form-select" required>
                  <option value="">-- Selecciona --</option>
                  <?php foreach($tipos as $t): ?>
                    <option value="<?= (int)$t['ID_TIPO_EVE'] ?>"><?= htmlspecialchars($t['NOM_TIPO_EVE']) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalNuevoTipo">
                  <i class="bi bi-plus-lg"></i>
                </button>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea id="descripcionEvento" name="DES_EVE_CUR" rows="3" class="form-control" placeholder="Breve descripción del evento"></textarea>
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
            <input id="insDesde" type="date" name="INSCRIPCION_DESDE" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Inscripción hasta</label>
            <input id="insHasta" type="date" name="INSCRIPCION_HASTA" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Inicio *</label>
            <input id="fecInicio" type="date" name="FEC_INI_EVE_CUR" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fin *</label>
            <input id="fecFin" type="date" name="FEC_FIN_EVE_CUR" class="form-control" required>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Lugar</label>
              <input id="lugar" name="LUGAR" class="form-control" placeholder="Auditorio FISEI">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ubicación/detalle</label>
              <input id="detalleLugar" name="UBICACION_DETALLE" class="form-control" placeholder="Bloque B, 2do piso">
            </div>

            <div class="col-md-3">
              <label class="form-label">Modalidad *</label>
              <select id="modalidad" name="MOD_EVE_CUR" class="form-select">
                <option value="Gratis">Gratis</option>
                <option value="Pagado">Pagado</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Costo ($)</label>
              <input id="costo" type="number" step="0.01" name="COS_EVE_CUR" class="form-control" value="0" disabled>
            </div>

            <div class="col-md-3">
              <label class="form-label">Capacidad máxima</label>
              <input id="capacidad" type="number" name="CAPACIDAD_MAXIMA" class="form-control" value="0" min="0">
              <div class="muted">Inicializa cupos disponibles.</div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Horas totales</label>
              <input id="horas" type="number" name="HORAS_TOTALES" class="form-control" min="0">
            </div>

            <div class="col-md-6">
              <label class="form-label">Responsable (Cédula)</label>
              <input id="responsable" name="RESPONSABLE_CED" class="form-control" placeholder="Ej. 0102030405">
            </div>
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
            <p class="muted mb-2">Selecciona el tipo de evento para cargar los requisitos.</p>
            <div class="table-responsive">
              <table class="table table-hover align-middle" id="tablaRequisitos">
                <thead>
                  <tr>
                    <th>Sel</th><th>Requisito</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($requisitos as $r): ?>
                    <tr>
                      <td><input class="reqCheck" type="checkbox" name="REQ_ID[]" value="<?= (int)$r['ID_REQ'] ?>"></td>
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
            <?php foreach($carreras as $c): ?>
              <div class="col-md-4 mb-2">
                <label class="req-chip">
                  <input class="carCheck" type="checkbox" name="CARRERAS[]" value="<?= (int)$c['ID_CARRERA'] ?>"> 
                  <?= htmlspecialchars($c['NOMBRE_CARRERA']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="muted mt-2">Si no seleccionas ninguna, el evento se considera abierto al público.</div>
        </div>
      </div>
    </div>
      

    <div class="accordion-item card mb-3">
      <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#p5" aria-expanded="true">
          <i class="bi bi-image me-2"></i>5) Imagen del evento
        </button>
      </h2>
      <div id="p5" class="accordion-collapse collapse show">
        <div class="accordion-body">
                    <input id="imgEvento" name="IMG_EVE_CUR" type="file" class="form-control" accept="image/*">


        </div>
      </div>
</div>

    <!-- Botón final -->
    <div class="card sticky-actions">
      <div class="text-end">
        <button id="btnGuardarEvento" type="submit" class="btn btn-uta px-4 py-2">
          <i class="bi bi-save me-1"></i>Guardar evento
        </button>
      </div>
    </div>
  </div>
</form>


 
<!-- Modal Nuevo Tipo -->
<div class="modal fade" id="modalNuevoTipo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-3">

      <!-- Título -->
      <div class="modal-header border-0">
        <h5 class="modal-title text-uta">
          <i class="bi bi-plus-circle me-2"></i>Nuevo tipo de evento
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Nombre del tipo -->
        <div class="mb-3">
          <label class="form-label">Nombre del tipo</label>
          <input id="nuevoTipoNombre" name="nombre_tipo" class="form-control">
        </div>

        <!-- Requisitos existentes -->
        <div class="mb-4">
          <label class="form-label">Requisitos existentes</label>

          <div id="listaRequisitosExistentes" class="border rounded p-2" style="max-height: 180px; overflow-y:auto;">
            <!-- Aquí se insertan los requisitos con check -->
            <!-- Ejemplo:
              <div class="form-check">
                <input class="form-check-input req-existente" type="checkbox" value="3">
                <label class="form-check-label">Documento de identidad</label>
              </div>
            -->
          </div>
        </div>

        <hr>

        <!-- Requisitos nuevos -->
        <label class="form-label">Crear requisitos nuevos</label>

        <div id="contenedorRequisitos">
          <div class="requisito-item mb-2 d-flex gap-2 align-items-center">
            <input type="text" class="form-control req-nombre" placeholder="Nombre del requisito">
            <select class="form-select req-tipo">
              <option value="NUMERICO">Numérico</option>
              <option value="TEXTO_CORTO">Texto corto</option>
              <option value="DOCUMENTO">Documento</option>
            </select>
            <input type="number" class="form-control req-valor-min" placeholder="Valor mínimo" style="display:none;">
            <button type="button" class="btn btn-danger btn-remove-requisito">X</button>
          </div>
        </div>

        <button type="button" id="agregarRequisito" class="btn btn-sm btn-uta mt-2">
          Agregar requisito
        </button>

      </div>

      <!-- Footer -->
      <div class="modal-footer border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button id="guardarNuevoTipo" class="btn btn-uta">Guardar</button>
      </div>

    </div>
  </div>
</div>

</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  // === Variables DOM ===
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

  const hoy = new Date().toISOString().split('T')[0];
  insDesde.setAttribute('min', hoy);
  insHasta.setAttribute('min', hoy);

  // === Cambiar costo según modalidad ===
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

  // === Cargar requisitos dinámicamente al cambiar tipo de evento ===
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
            tbody.innerHTML += `
              <tr>
                <td><input class="reqCheck" type="checkbox" name="REQ_ID[]" value="${req.ID_REQ}"></td>
                <td>${req.NOM_REQ}</td>
           
              </tr>`;
          });
        })
        .catch(err => console.error('Error cargando requisitos:', err));
    });
  }

  // === Modal de nuevo tipo de evento (requisitos dinámicos) ===
  if (contenedorReqModal) {
    // Mostrar campo valor mínimo solo si NUMERICO
    contenedorReqModal.addEventListener('change', (e) => {
      if (e.target.classList.contains('req-tipo')) {
        const parent = e.target.closest('.requisito-item');
        const valorMin = parent.querySelector('.req-valor-min');
        valorMin.style.display = e.target.value === 'NUMERICO' ? 'block' : 'none';
      }
    });

    // Eliminar requisito
    contenedorReqModal.addEventListener('click', (e) => {
      if (e.target.classList.contains('btn-remove-requisito')) {
        e.target.closest('.requisito-item').remove();
      }
    });

    // Agregar requisito
    const btnAgregarReq = document.getElementById('agregarRequisito');
    if (btnAgregarReq) {
      btnAgregarReq.addEventListener('click', () => {
        const template = contenedorReqModal.querySelector('.requisito-item').cloneNode(true);
        template.querySelectorAll('input').forEach(inp => inp.value = '');
        template.querySelector('select').value = 'NUMERICO';
        template.querySelector('.req-valor-min').style.display = 'block';
        contenedorReqModal.appendChild(template);
      });
    }
  }

  // === Cargar requisitos existentes al abrir el modal ===
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
          lista.innerHTML += `
            <div class="form-check">
              <input class="form-check-input req-existente" type="checkbox" value="${req.ID_REQ}">
              <label class="form-check-label">${req.NOM_REQ}</label>
            </div>`;
        });
      })
      .catch(err => console.error('Error cargando requisitos existentes:', err));
  });
}

  // === Guardar nuevo tipo de evento (modal) ===
  if (btnGuardarNuevoTipo) {
    btnGuardarNuevoTipo.addEventListener('click', async () => {
      const nombre = document.getElementById('nuevoTipoNombre').value.trim();
      //const img = document.getElementById('nuevoTipoImagen').files[0];
      if (!nombre) return alert('Debes ingresar el nombre del tipo.');

      const formData = new FormData();
      formData.append('nombre_tipo', nombre);
      //if (img) formData.append('imagen_tipo', img);

      // Requisitos
      const requisitos = [];
      contenedorReqModal.querySelectorAll('.requisito-item').forEach(item => {
        const nom = item.querySelector('.req-nombre').value.trim();
        const tipo = item.querySelector('.req-tipo').value;
        const valMin = item.querySelector('.req-valor-min').value || null;
        if (nom) requisitos.push({ nombre: nom, tipo, valor_min: tipo === 'NUMERICO' ? valMin : null });
      });
      formData.append('requisitos', JSON.stringify(requisitos));
// Obtener requisitos existentes marcados
const reqExistentesMarcados = [];
document.querySelectorAll('.req-existente:checked').forEach(cb => {
  reqExistentesMarcados.push(cb.value);
});

formData.append('requisitos_existentes', JSON.stringify(reqExistentesMarcados));

      try {
        const res = await fetch('guardarTipoEvento.php', { method: 'POST', body: formData });
        const json = await res.json();
        if (json.success) {
          alert('Tipo de evento guardado.');
          location.reload();
        } else {
          alert('Error: ' + json.message);
        }
      } catch (e) {
        console.error('Error al guardar tipo:', e);
        alert('Error inesperado al guardar el tipo de evento.');
      }
    });
  }

  // === Guardar evento principal ===
  if (formEvento && btnGuardarEvento) {
    btnGuardarEvento.addEventListener('click', async (e) => {
      e.preventDefault();

      // Validaciones de fechas antes de enviar
      if (insDesde.value && insHasta.value && insHasta.value < insDesde.value) {
        return alert('La fecha de inscripción hasta no puede ser antes de la fecha de inicio de inscripción.');
      }
      if (fecInicio.value && insHasta.value && fecInicio.value < insHasta.value) {
        return alert('La fecha de inicio del evento no puede ser antes de la fecha de fin de inscripción.');
      }
      if (fecFin.value && fecInicio.value && fecFin.value < fecInicio.value) {
        return alert('La fecha de finalización del evento no puede ser antes de la fecha de inicio.');
      }

      const data = new FormData(formEvento);

      // Agregar requisitos seleccionados
      document.querySelectorAll('.reqCheck:checked').forEach(cb => data.append('REQ_ID[]', cb.value));

      // Agregar carreras seleccionadas
      document.querySelectorAll('.carCheck:checked').forEach(cb => data.append('CARRERAS[]', cb.value));

      try {
        const res = await fetch('guardarEvento.php', { method: 'POST', body: data });
           const ct = res.headers.get('content-type') || '';
      const payload = ct.includes('application/json') ? await res.json() : { success:false, message: await res.text() };
        if (payload.success) {
          alert('Evento guardado correctamente.');
          formEvento.reset();
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
