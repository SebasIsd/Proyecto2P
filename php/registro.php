<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
    $carrera_id = filter_input(INPUT_POST, 'carrera_id', FILTER_VALIDATE_INT) ?: null;

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO USUARIO (nombre, apellido, email, password_hash, telefono, direccion, carrera_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $email, $password, $telefono, $direccion, $carrera_id]);
        $user_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("SELECT id_rol FROM ROL WHERE nombre_rol = 'Participante'");
        $stmt->execute();
        $rol_id = $stmt->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO USUARIO_ROL (id_usuario, id_rol) VALUES (?, ?)");
        $stmt->execute([$user_id, $rol_id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'Error al registrar: ' . $e->getMessage()]);
    }
}
?>