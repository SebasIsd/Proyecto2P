<?php
// home/guardar_footer.php
header('Content-Type: application/json');
include __DIR__ . '/../includes/conexion.php'; // Ajusta ruta si es necesario

$response = ['status' => 'error', 'msg' => 'No se pudo guardar la información.'];

// Validar que se recibieron todos los campos esperados
$campos = ['telefono', 'correo', 'Des_logo', 'face', 'ins_gra', 'dias', 'horas', 'derechos'];
$data = [];

foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) {
        $response['msg'] = "Falta el campo $campo.";
        echo json_encode($response);
        exit;
    }
    $data[$campo] = $_POST[$campo];
}

// Preparar la actualización en la BD
$sql = "UPDATE footer SET 
            telefono = ?, 
            correo = ?, 
            Des_logo = ?, 
            face = ?, 
            ins_gra = ?, 
            dias = ?, 
            horas = ?, 
            derechos = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param(
        "ssssssss",
        $data['telefono'],
        $data['correo'],
        $data['Des_logo'],
        $data['face'],
        $data['ins_gra'],
        $data['dias'],
        $data['horas'],
        $data['derechos']
    );

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'msg' => 'Footer actualizado correctamente.'];
    } else {
        $response['msg'] = 'Error al ejecutar la consulta.';
    }

    $stmt->close();
} else {
    $response['msg'] = 'Error en la preparación de la consulta.';
}

echo json_encode($response);
?>
