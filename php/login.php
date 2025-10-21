<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id_usuario, password_hash, activo FROM USUARIO WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash']) && $user['activo']) {
        $stmt = $pdo->prepare("SELECT r.nombre_rol FROM USUARIO_ROL ur JOIN ROL r ON ur.id_rol = r.id_rol WHERE ur.id_usuario = ?");
        $stmt->execute([$user['id_usuario']]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['roles'] = $roles;
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas o usuario inactivo']);
    }
}
?>