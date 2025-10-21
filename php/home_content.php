<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM HOME_CONTENT WHERE id = 1");
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array('Administrador', $_SESSION['roles'] ?? [])) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $contenido = filter_input(INPUT_POST, 'contenido', FILTER_SANITIZE_STRING);
    $imagen = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $imagen = $upload_dir . uniqid() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    }

    $stmt = $pdo->prepare("UPDATE HOME_CONTENT SET titulo = ?, contenido = ?, imagen = ?, updated_at = NOW() WHERE id = 1");
    $stmt->execute([$titulo, $contenido, $imagen]);
    echo json_encode(['success' => true]);
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
}
?>