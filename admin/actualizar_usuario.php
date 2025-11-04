<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    exit(json_encode(['success' => false, 'message' => 'No autorizado']));
}

require_once '../includes/conexion.php';

$cedula = $_POST['cedula'] ?? '';
if (!$cedula) exit(json_encode(['success' => false, 'message' => 'Falta cédula']));

$updates = []; $params = []; $types = '';
$campos = ['nom_pri', 'nom_seg', 'ape_pri', 'ape_seg', 'correo', 'telefono', 'direccion', 'rol_id', 'carrera_id', 'activo'];
foreach ($campos as $campo) {
    if (isset($_POST[$campo])) {
        $val = $_POST[$campo] === '' ? null : trim($_POST[$campo]);
        if ($campo === 'activo') $val = $val ? 1 : 0;
        $updates[] = "$campo = ?";
        $params[] = $val;
        $types .= is_null($val) ? 's' : (in_array($campo, ['rol_id', 'carrera_id', 'activo']) ? 'i' : 's');
    }
}

if (!empty($_POST['clave']) && strlen($_POST['clave']) >= 6) {
    $updates[] = "PAS_USU = ?";
    $params[] = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    $types .= 's';
}

$updates[] = "CED_USU = ?"; $params[] = $cedula; $types .= 's';

$sql = "UPDATE USUARIOS SET " . implode(', ', $updates) . " WHERE CED_USU = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

echo json_encode([
    'success' => $stmt->execute(),
    'message' => $stmt->execute() ? 'Usuario actualizado' : 'Error al guardar'
]);