<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
  header("Location: ../index.php"); exit();
}
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_evento = (int)($_POST['id_evento'] ?? 0);

  if ($id_evento > 0) {
    $stmt = $conn->prepare("UPDATE EVENTOS_CURSOS SET NOTAS_FINALIZADAS = 1 WHERE ID_EVE_CUR = ?");
    $stmt->bind_param("i", $id_evento);
    $stmt->execute();
    $stmt->close();
  }
}

header("Location: evidencias_global.php?evento=".$id_evento."&tipo=NUMERICO");
exit();
?>