<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  http_response_code(401);
  echo json_encode(["success"=>false,"message"=>"No autorizado"]); exit;
}

require_once __DIR__ . '/../includes/conexion.php';

try {
  $id = (int)($_GET['id'] ?? 0);
  if ($id <= 0) throw new Exception('ID inválido');

  $q = $conn->prepare("
    SELECT ID_EVE_CUR, TIT_EVE_CUR, DES_EVE_CUR, INSCRIPCION_DESDE, INSCRIPCION_HASTA,
           FEC_INI_EVE_CUR, FEC_FIN_EVE_CUR, MOD_EVE_CUR, COS_EVE_CUR, LUGAR, UBICACION_DETALLE,
           CAPACIDAD_MAXIMA, CUPOS_DISPONIBLES, HORAS_TOTALES, ID_TIPO_EVE, RESPONSABLE_CED, ACTIVO
    FROM EVENTOS_CURSOS WHERE ID_EVE_CUR=?
  ");
  $q->bind_param("i", $id);
  $q->execute();
  $ev = $q->get_result()->fetch_assoc();
  if (!$ev) throw new Exception('Evento no encontrado');

  // Requisitos ya seleccionados
  $reqIds = [];
  $rs = $conn->prepare("SELECT ID_REQ FROM EVENTOS_REQUISITOS WHERE ID_EVE_CUR=?");
  $rs->bind_param("i", $id);
  $rs->execute();
  $r = $rs->get_result();
  while($row = $r->fetch_assoc()) $reqIds[] = (int)$row['ID_REQ'];

  // Carreras ya seleccionadas
  $carIds = [];
  $cs = $conn->prepare("SELECT ID_CARRERA FROM EVENTOS_CARRERAS WHERE ID_EVE_CUR=?");
  $cs->bind_param("i", $id);
  $cs->execute();
  $cr = $cs->get_result();
  while($row = $cr->fetch_assoc()) $carIds[] = (int)$row['ID_CARRERA'];

  $ev['REQ_IDS'] = $reqIds;
  $ev['CARRERAS'] = $carIds;

  echo json_encode(["success"=>true,"data"=>$ev]); exit;
} catch(Throwable $e){
  http_response_code(400);
  echo json_encode(["success"=>false,"message"=>$e->getMessage()]); exit;
}
