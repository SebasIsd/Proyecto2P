<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/conexion.php'; // tu conexión existente

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}


$nombre_tipo = trim($_POST['nombre_tipo'] ?? '');
$requisitos = $_POST['requisitos'] ?? [];
$nuevo_requisito = trim($_POST['nuevo_requisito'] ?? '');


if (!$nombre_tipo) {
    echo json_encode(["success" => false, "message" => "El nombre del tipo es obligatorio."]);
    exit;
}


$uploadDir = __DIR__ . '/../images/eventos/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$imagenPath = null;
if (!empty($_FILES['imagen']['name'])) {
    $file = $_FILES['imagen'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(["success" => false, "message" => "Formato de imagen no permitido."]);
        exit;
    }

    // Nombre original limpio
    $filename = basename($file['name']);
    $filename = preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $filename);

    // Evitar sobrescribir
    $target = $uploadDir . $filename;
    $counter = 1;
    while(file_exists($target)) {
        $filename = pathinfo($file['name'], PATHINFO_FILENAME) . "_$counter." . $ext;
        $target = $uploadDir . $filename;
        $counter++;
    }

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        echo json_encode(["success" => false, "message" => "Error al subir la imagen."]);
        exit;
    }

    $imagenPath = 'images/eventos/' . $filename;
}


$conn->begin_transaction();

try {
    // Insertar tipo de evento
    $stmt = $conn->prepare("INSERT INTO TIPOS_EVENTO (NOM_TIPO_EVE, IMG_TIPO_EVE) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre_tipo, $imagenPath);
    $stmt->execute();
    $id_tipo = $stmt->insert_id;

    // Insertar nuevo requisito si existe
    if ($nuevo_requisito) {
        $stmt_req = $conn->prepare("INSERT INTO REQUISITOS (NOM_REQ, DES_REQ, TIPO) VALUES (?, ?, 'TEXTO_CORTO')");
        $stmt_req->bind_param("ss", $nuevo_requisito, $nuevo_requisito);
        $stmt_req->execute();
        $nuevo_id_req = $stmt_req->insert_id;
        $requisitos[] = $nuevo_id_req;
    }

    // Asociar requisitos existentes al tipo
    if (!empty($requisitos)) {
        $stmt_rel = $conn->prepare("INSERT INTO TIPOS_EVENTO_REQUISITOS (ID_TIPO_EVE, ID_REQ) VALUES (?, ?)");
        foreach ($requisitos as $id_req) {
            $id_req = intval($id_req);
            $stmt_rel->bind_param("ii", $id_tipo, $id_req);
            $stmt_rel->execute();
        }
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Tipo de evento guardado correctamente."]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
