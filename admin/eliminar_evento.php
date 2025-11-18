<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  http_response_code(403);
  echo json_encode(["success"=>false,"message"=>"No autorizado"]); exit;
}
require_once __DIR__ . '/../includes/conexion.php';

try{
  if($_SERVER['REQUEST_METHOD']!=='POST') throw new Exception('Método no permitido');
  $id = (int)($_POST['id'] ?? 0);
  if($id<=0) throw new Exception('ID inválido');

  $stmt = $conn->prepare("DELETE FROM EVENTOS_CURSOS WHERE ID_EVE_CUR=?");
  $stmt->bind_param("i", $id);
  if(!$stmt->execute()) throw new Exception('No se pudo eliminar el evento');

  echo json_encode(["success"=>true]);
}catch(Throwable $e){
  http_response_code(400);
  echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
?>