<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/conexion.php';

if (isset($_GET['id_tipo'])) {
    $id_tipo = intval($_GET['id_tipo']);

    // Consulta los requisitos relacionados al tipo de evento
    $sql = "
        SELECT r.ID_REQ, r.NOM_REQ, r.DES_REQ, r.TIPO
        FROM TIPOS_EVENTO_REQUISITOS ter
        INNER JOIN REQUISITOS r ON ter.ID_REQ = r.ID_REQ
        WHERE ter.ID_TIPO_EVE = ?
        ORDER BY r.NOM_REQ ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_tipo);
    $stmt->execute();
    $result = $stmt->get_result();

    $requisitos = [];
    while ($row = $result->fetch_assoc()) {
        $requisitos[] = $row;
    }

    echo json_encode($requisitos, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "No se proporcionó el id_tipo."]);
}
?>
