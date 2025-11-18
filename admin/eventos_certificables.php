<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  header("Location: ../index.php"); exit();
}

require_once __DIR__ . '/../includes/conexion.php';

/*
 * Eventos aptos para certificación:
 * - Evento ya finalizado (FEC_FIN_EVE_CUR <= CURDATE())
 * - Contar inscripciones
 * - Contar inscripciones que cumplen TODOS los requisitos obligatorios
 *   y (si es Pagado) tienen pago aprobado.
 */

$sql = "
SELECT
  e.ID_EVE_CUR,
  e.TIT_EVE_CUR,
  e.FEC_INI_EVE_CUR,
  e.FEC_FIN_EVE_CUR,
  e.MOD_EVE_CUR,
  te.NOM_TIPO_EVE,
  e.HORAS_TOTALES,
  COUNT(DISTINCT i.ID_INS) AS total_inscritos,
  SUM(
    CASE
      WHEN i.ESTADO_INS = 'Completado'
           /* Pago (solo si es evento pagado) */
           AND (
             e.MOD_EVE_CUR = 'Gratis'
             OR EXISTS (
               SELECT 1 FROM PAGOS p
               WHERE p.ID_INS = i.ID_INS
                 AND p.ESTADO_VALIDACION = 'Aprobado'
             )
           )
           /* Requisitos obligatorios cumplidos */
           AND NOT EXISTS (
             SELECT 1
             FROM EVENTOS_REQUISITOS er
             LEFT JOIN EVIDENCIAS ev
               ON ev.ID_REQ = er.ID_REQ
              AND ev.ID_INS = i.ID_INS
             WHERE er.ID_EVE_CUR = e.ID_EVE_CUR
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
      THEN 1 ELSE 0
    END
  ) AS total_apto
FROM EVENTOS_CURSOS e
JOIN TIPOS_EVENTO te ON te.ID_TIPO_EVE = e.ID_TIPO_EVE
JOIN INSCRIPCIONES i ON i.ID_EVE_CUR = e.ID_EVE_CUR
WHERE e.FEC_FIN_EVE_CUR <= CURDATE()
GROUP BY
  e.ID_EVE_CUR,
  e.TIT_EVE_CUR,
  e.FEC_INI_EVE_CUR,
  e.FEC_FIN_EVE_CUR,
  e.MOD_EVE_CUR,
  te.NOM_TIPO_EVE,
  e.HORAS_TOTALES
ORDER BY e.FEC_FIN_EVE_CUR DESC
";

$evRes = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Eventos aptos para certificados</title>
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

  <div class="content">
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="mb-1">Eventos aptos para generación de certificados</h3>
        <div class="text-muted">
          Se muestran los eventos que ya finalizaron y el número de participantes que cumplen
          con todos los requisitos (evidencias y pagos aprobados, en caso de eventos pagados).
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Listado de eventos</div>
      <div class="card-body table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Evento</th>
              <th>Tipo</th>
              <th>Fechas</th>
              <th>Modalidad</th>
              <th>Horas</th>
              <th>Inscritos</th>
              <th>Aptos para certificado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$evRes || $evRes->num_rows === 0): ?>
              <tr>
                <td colspan="8" class="text-center text-muted">
                  No hay eventos finalizados o con inscripciones registradas.
                </td>
              </tr>
            <?php else: ?>
              <?php while($e = $evRes->fetch_assoc()):
                $inscritos = (int)$e['total_inscritos'];
                $aptos     = (int)$e['total_apto'];
                $fechaRango = date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) .
                              ' - ' .
                              date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR']));
              ?>
                <tr>
                  <td><div class="fw-semibold"><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></div></td>
                  <td><span class="badge-soft"><?= htmlspecialchars($e['NOM_TIPO_EVE']) ?></span></td>
                  <td><?= $fechaRango ?></td>
                  <td>
                    <?php if ($e['MOD_EVE_CUR'] === 'Pagado'): ?>
                      <span class="badge bg-success">Pagado</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Gratis</span>
                    <?php endif; ?>
                  </td>
                  <td><?= (int)$e['HORAS_TOTALES'] ?> h</td>
                  <td><?= $inscritos ?></td>
                  <td>
                    <?php if ($aptos > 0): ?>
                      <span class="badge bg-success"><?= $aptos ?> apto(s)</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">0 aptos</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="certificados_lista.php?evento=<?= (int)$e['ID_EVE_CUR'] ?>"
                       class="btn btn-sm btn-outline-primary">
                      <i class="fa fa-users"></i> Ver participantes
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
