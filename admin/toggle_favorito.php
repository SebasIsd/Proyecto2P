<?php
// toggle_favorito.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success'=>false,'message'=>'Método no permitido']); exit;
}

// SOLO ADMIN
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'No autorizado']); exit;
}

require_once '../includes/conexion.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID inválido']); exit; }

// Verifica que exista
$ex = $conn->prepare("SELECT 1 FROM EVENTOS_CURSOS WHERE ID_EVE_CUR=?");
$ex->bind_param('i',$id); $ex->execute(); $ex->store_result();
if ($ex->num_rows===0) { echo json_encode(['success'=>false,'message'=>'Evento no encontrado']); exit; }
$ex->close();

// ¿Ya está marcado?
$sel = $conn->prepare("SELECT 1 FROM EVENTOS_FAVORITOS WHERE ID_EVE_CUR=?");
$sel->bind_param('i',$id); $sel->execute(); $sel->store_result();
$ya = $sel->num_rows>0; $sel->close();

if ($ya) {
  $del = $conn->prepare("DELETE FROM EVENTOS_FAVORITOS WHERE ID_EVE_CUR=?");
  $del->bind_param('i',$id);
  $ok = $del->execute();
  echo json_encode(['success'=>$ok,'favorito'=>false]); exit;
} else {
  $ins = $conn->prepare("INSERT INTO EVENTOS_FAVORITOS (ID_EVE_CUR) VALUES (?)");
  $ins->bind_param('i',$id);
  $ok = $ins->execute();
  echo json_encode(['success'=>$ok,'favorito'=>true]); exit;
}
