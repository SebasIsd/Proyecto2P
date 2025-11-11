<?php
error_reporting(E_ALL);
ini_set('display_errors', '0'); // evitar HTML en la salida
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/conexion.php';

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gestionar_eventos.php?err=' . urlencode('Método no permitido'));
    exit;
  }

  // === Datos principales ===
  $idEvento    = (int)($_POST['ID_EVE_CUR'] ?? 0);
  $titulo      = trim($_POST['TIT_EVE_CUR'] ?? '');
  $descripcion = $_POST['DES_EVE_CUR'] ?? '';
  $insDesde    = $_POST['INSCRIPCION_DESDE'] ?? null;
  $insHasta    = $_POST['INSCRIPCION_HASTA'] ?? null;
  $fecIni      = $_POST['FEC_INI_EVE_CUR'] ?? null;
  $fecFin      = $_POST['FEC_FIN_EVE_CUR'] ?? null;
  $modalidad   = $_POST['MOD_EVE_CUR'] ?? 'Gratis';
  $costo       = (float)($_POST['COS_EVE_CUR'] ?? 0);
  $lugar       = $_POST['LUGAR'] ?? '';
  $ubicacion   = $_POST['UBICACION_DETALLE'] ?? '';
  $capacidad   = (int)($_POST['CAPACIDAD_MAXIMA'] ?? 0);
  $cupos       = (int)($_POST['CUPOS_DISPONIBLES'] ?? 0);
  $horas       = ($_POST['HORAS_TOTALES'] === '' ? null : (int)$_POST['HORAS_TOTALES']);
  $idTipo      = (int)($_POST['ID_TIPO_EVE'] ?? 0);
  $responsable = trim($_POST['RESPONSABLE_CED'] ?? '');
  $activo      = isset($_POST['ACTIVO']) ? (int)$_POST['ACTIVO'] : 1;

  $reqIds      = $_POST['REQ_ID'] ?? $_POST['REQ_ID__'] ?? $_POST['REQ_ID[]'] ?? [];
  $carIds      = $_POST['CARRERAS'] ?? $_POST['CARRERAS__'] ?? $_POST['CARRERAS[]'] ?? [];

  if ($idEvento<=0 || $titulo==='' || !$fecIni || !$fecFin || $idTipo<=0){
    throw new Exception("Faltan datos obligatorios.");
  }

  // Validaciones de fecha (similar a crear)
  $hoy = date('Y-m-d');
  if ($insDesde && $insDesde < $hoy) {
    // Si quieres permitir editar a pasado, comenta esta línea
    // throw new Exception("Inscripción desde no puede ser antes de hoy.");
  }
  if ($insHasta && $insDesde && $insHasta < $insDesde) {
    throw new Exception("Inscripción hasta no puede ser antes que inscripción desde.");
  }
  if ($fecIni && $insHasta && $fecIni < $insHasta) {
    throw new Exception("Inicio del evento no puede ser antes del fin de inscripción.");
  }
  if ($fecFin && $fecIni && $fecFin < $fecIni) {
    throw new Exception("Fin del evento no puede ser antes del inicio.");
  }

  // Validar responsable (si viene)
  if ($responsable==='') $responsable = null;
  if ($responsable !== null) {
    $chk = $conn->prepare("SELECT 1 FROM USUARIOS WHERE CED_USU=?");
    $chk->bind_param("s", $responsable);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows===0) $responsable = null;
    $chk->close();
  }

  $conn->begin_transaction();

  // === UPDATE principal ===
  $stmt = $conn->prepare("
    UPDATE EVENTOS_CURSOS
       SET TIT_EVE_CUR=?, DES_EVE_CUR=?, INSCRIPCION_DESDE=?, INSCRIPCION_HASTA=?,
           FEC_INI_EVE_CUR=?, FEC_FIN_EVE_CUR=?, MOD_EVE_CUR=?, COS_EVE_CUR=?,
           LUGAR=?, UBICACION_DETALLE=?, CAPACIDAD_MAXIMA=?, CUPOS_DISPONIBLES=?,
           HORAS_TOTALES=?, ID_TIPO_EVE=?, RESPONSABLE_CED=?, ACTIVO=?
     WHERE ID_EVE_CUR=?
  ");
  $stmt->bind_param(
    "ssssssssssiiisiii",
    $titulo, $descripcion, $insDesde, $insHasta,
    $fecIni, $fecFin, $modalidad, $costo,
    $lugar, $ubicacion, $capacidad, $cupos,
    $horas, $idTipo, $responsable, $activo,
    $idEvento
  );
  if (!$stmt->execute()) throw new Exception("No se pudo actualizar el evento.");

  // === Sincronizar requisitos (EVENTOS_REQUISITOS) ===
  $idsReq = [];
  foreach((array)$reqIds as $v){ $idsReq[] = (int)$v; }
  $idsReq = array_values(array_unique(array_filter($idsReq)));

  // Borrar los que ya no están
  if (!empty($idsReq)){
    $in = implode(',', $idsReq);
    $conn->query("DELETE FROM EVENTOS_REQUISITOS WHERE ID_EVE_CUR=$idEvento AND ID_REQ NOT IN ($in)");
  } else {
    $conn->query("DELETE FROM EVENTOS_REQUISITOS WHERE ID_EVE_CUR=$idEvento");
  }
  // Insertar faltantes
  if (!empty($idsReq)){
    $insReq = $conn->prepare("INSERT IGNORE INTO EVENTOS_REQUISITOS (ID_EVE_CUR, ID_REQ, OBLIGATORIO) VALUES (?, ?, 1)");
    foreach($idsReq as $rid){
      $insReq->bind_param("ii", $idEvento, $rid);
      $insReq->execute();
    }
  }

  // === Sincronizar carreras (EVENTOS_CARRERAS) ===
  $idsCar = [];
  foreach((array)$carIds as $v){ $idsCar[] = (int)$v; }
  $idsCar = array_values(array_unique(array_filter($idsCar)));

  if (!empty($idsCar)){
    $in = implode(',', $idsCar);
    $conn->query("DELETE FROM EVENTOS_CARRERAS WHERE ID_EVE_CUR=$idEvento AND ID_CARRERA NOT IN ($in)");
  } else {
    $conn->query("DELETE FROM EVENTOS_CARRERAS WHERE ID_EVE_CUR=$idEvento");
  }
  if (!empty($idsCar)){
    $insCar = $conn->prepare("INSERT IGNORE INTO EVENTOS_CARRERAS (ID_EVE_CUR, ID_CARRERA) VALUES (?, ?)");
    foreach($idsCar as $cid){
      $insCar->bind_param("ii", $idEvento, $cid);
      $insCar->execute();
    }
  }

  $conn->commit();
 header('Location: gestionar_eventos.php?ok=1');
  exit;

} catch (Throwable $e) {
  @ $conn->rollback();
  header('Location: gestionar_eventos.php?err=' . urlencode($e->getMessage()));
  exit; echo json_encode(["success"=>false,"message"=>"Error: ".$e->getMessage()]); exit;
}
?>