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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
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
  <div class="container py-5">
    <div class="brand-header">
      <h2><i class="bi bi-calendar2-plus me-2"></i>Crear nuevo evento o curso</h2>
      <p>Completa la información paso a paso</p>
    </div>

    <form method="post" id="formEvento">
      <div class="accordion" id="eventWizard">

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
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p2" aria-expanded="true">
      <i class="bi bi-geo-alt me-2"></i>2) Fechas, lugar y modalidad
    </button>
  </h2>
  <div id="p2" class="accordion-collapse collapse">
    <div class="accordion-body">

      <!-- Fechas: columna completa -->
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

      <!-- Resto de campos en dos columnas -->
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
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p3" aria-expanded="true">
        <i class="bi bi-list-check me-2"></i>3) Requisitos del evento
      </button>
    </h2>
    <div id="p3" class="accordion-collapse collapse">
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
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#p4" aria-expanded="true">
        <i class="bi bi-mortarboard me-2"></i>4) Carreras destinatarias
      </button>
    </h2>
    <div id="p4" class="accordion-collapse collapse">
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
  </div>

  <!-- Modal Nuevo Tipo -->
  <!-- Modal Nuevo Tipo -->
<div class="modal fade" id="modalNuevoTipo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header border-0">
        <h5 class="modal-title text-uta">
          <i class="bi bi-plus-circle me-2"></i>Nuevo tipo de evento
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre del tipo</label>
          <input id="nuevoTipoNombre" name="nombre_tipo" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Imagen del tipo</label>
          <input id="nuevoTipoImagen" type="file" class="form-control" accept="image/*">
        </div>

        <!-- Sección de requisitos dinámicos -->
        <div class="mb-3">
          <label class="form-label">Requisitos del tipo</label>
          <div id="contenedorRequisitos">
            <!-- Plantilla de requisito -->
            <div class="requisito-item mb-2 d-flex gap-2">
              <input type="text" class="form-control req-nombre" placeholder="Nombre del requisito">
              <select class="form-select req-tipo">
                <option value="NUMERICO">Numérico</option>
                <option value="TEXTO_CORTO">Texto corto</option>
                <option value="DOCUMENTO">Documento</option>
              </select>
              <input type="number" class="form-control req-valor-min" placeholder="Valor mínimo" style="display:none;">
              <button type="button" class="btn btn-danger btn-remove-requisito">Eliminar</button>
            </div>
          </div>
          <button type="button" id="agregarRequisito" class="btn btn-sm btn-uta mt-2">Agregar requisito</button>
        </div>
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

  // === Guardar nuevo tipo de evento (modal) ===
  if (btnGuardarNuevoTipo) {
    btnGuardarNuevoTipo.addEventListener('click', async () => {
      const nombre = document.getElementById('nuevoTipoNombre').value.trim();
      const img = document.getElementById('nuevoTipoImagen').files[0];
      if (!nombre) return alert('Debes ingresar el nombre del tipo.');

      const formData = new FormData();
      formData.append('nombre_tipo', nombre);
      if (img) formData.append('imagen_tipo', img);

      // Requisitos
      const requisitos = [];
      contenedorReqModal.querySelectorAll('.requisito-item').forEach(item => {
        const nom = item.querySelector('.req-nombre').value.trim();
        const tipo = item.querySelector('.req-tipo').value;
        const valMin = item.querySelector('.req-valor-min').value || null;
        if (nom) requisitos.push({ nombre: nom, tipo, valor_min: tipo === 'NUMERICO' ? valMin : null });
      });
      formData.append('requisitos', JSON.stringify(requisitos));

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
        const text = await res.text();
        console.log('Respuesta guardarEvento.php:', text);
        const json = JSON.parse(text);
        if (json.success) {
          alert('Evento guardado correctamente.');
          formEvento.reset();
        } else {
          alert('Error: ' + json.message);
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
