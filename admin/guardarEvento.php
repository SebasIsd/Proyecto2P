<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/conexion.php';

try {
  // Leer datos del formulario
  $titulo = $_POST['TIT_EVE_CUR'] ?? '';
  $descripcion = $_POST['DES_EVE_CUR'] ?? '';
  $fechaInicio = $_POST['FEC_INI_EVE_CUR'] ?? null;
  $fechaFin = $_POST['FEC_FIN_EVE_CUR'] ?? null;
  $modalidad = $_POST['MOD_EVE_CUR'] ?? 'Gratis';
  $costo = $_POST['COS_EVE_CUR'] ?? 0;
  $lugar = $_POST['LUGAR'] ?? '';
  $detalle = $_POST['UBICACION_DETALLE'] ?? '';
  $capacidad = $_POST['CAPACIDAD_MAXIMA'] ?? 0;
  $cupos = $_POST['CUPOS_DISPONIBLES'] ?? 0;
  $horas = $_POST['HORAS_TOTALES'] ?? null;
  $idTipo = $_POST['ID_TIPO_EVE'] ?? null;
  $responsable = $_POST['RESPONSABLE_CED'] ?? null;

  $reqIds = $_POST['REQUISITOS'] ?? [];
  $carreras = $_POST['CARRERAS'] ?? [];

  if (!$titulo || !$fechaInicio || !$fechaFin || !$idTipo) {
    throw new Exception("Faltan datos obligatorios");
  }

  // Iniciar transacción
  $conn->begin_transaction();

  // Insertar evento principal
  $stmt = $conn->prepare("
    INSERT INTO EVENTOS_CURSOS
    (TIT_EVE_CUR, DES_EVE_CUR, FEC_INI_EVE_CUR, FEC_FIN_EVE_CUR, MOD_EVE_CUR,
     COS_EVE_CUR, LUGAR, UBICACION_DETALLE, CAPACIDAD_MAXIMA, CUPOS_DISPONIBLES,
     HORAS_TOTALES, ID_TIPO_EVE, RESPONSABLE_CED)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->bind_param(
    "sssssdssiiisi",
    $titulo, $descripcion, $fechaInicio, $fechaFin, $modalidad, $costo,
    $lugar, $detalle, $capacidad, $cupos, $horas, $idTipo, $responsable
  );
  $stmt->execute();

  $id_evento = $conn->insert_id;

  // Insertar carreras relacionadas
  $carreras = array_unique(array_filter($carreras));
  if (!empty($carreras)) {
    $stmtCar = $conn->prepare("
      INSERT IGNORE INTO EVENTOS_CARRERAS (ID_EVE_CUR, ID_CARRERA)
      VALUES (?, ?)
    ");
    foreach ($carreras as $idCarrera) {
      $stmtCar->bind_param("ii", $id_evento, $idCarrera);
      $stmtCar->execute();
    }
  }

  // Insertar requisitos relacionados
  $reqIds = array_unique(array_filter($reqIds));
  if (!empty($reqIds)) {
    $stmtReq = $conn->prepare("
      INSERT IGNORE INTO EVENTOS_REQUISITOS (ID_EVE_CUR, ID_REQ, OBLIGATORIO)
      VALUES (?, ?, 1)
    ");
    foreach ($reqIds as $idReq) {
      $stmtReq->bind_param("ii", $id_evento, $idReq);
      $stmtReq->execute();
    }
  }

  // Registrar responsable como personal del evento (opcional)
  if (!empty($responsable)) {
    $stmtPer = $conn->prepare("
      INSERT IGNORE INTO PERSONAL_EVENTO (ID_EVE_CUR, CED_USU, ROL_EVENTO, ES_RESPONSABLE)
      VALUES (?, ?, 'PONENTE', 1)
    ");
    $stmtPer->bind_param("is", $id_evento, $responsable);
    $stmtPer->execute();
  }

  // Confirmar todo
  $conn->commit();

  echo json_encode([
    "success" => true,
    "message" => "Evento guardado correctamente",
    "id_evento" => $id_evento
  ]);

} catch (Exception $e) {
  if (method_exists($conn, 'rollback')) {
    $conn->rollback();
  }
  http_response_code(400);
  echo json_encode([
    "success" => false,
    "message" => "Error al guardar el evento: " . $e->getMessage()
  ]);
}
?>
