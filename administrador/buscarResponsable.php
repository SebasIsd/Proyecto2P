<?php
require_once __DIR__ . '/../includes/conexion.php'; // Conexión a la BD

header('Content-Type: application/json; charset=utf-8');

$search = isset($_GET['term']) ? "%" . $_GET['term'] . "%" : "%";

$sql = "SELECT 
            CED_USU AS ced,
            CONCAT(NOM_PRI_USU, ' ', IFNULL(NOM_SEG_USU,'')) AS nombres,
            CONCAT(APE_PRI_USU, ' ', IFNULL(APE_SEG_USU,'')) AS apellidos,
            COR_USU AS correo
        FROM USUARIOS
        WHERE ACTIVO = 1
          AND (
                CED_USU LIKE ?
             OR NOM_PRI_USU LIKE ?
             OR NOM_SEG_USU LIKE ?
             OR APE_PRI_USU LIKE ?
             OR APE_SEG_USU LIKE ?
              )
        ORDER BY APE_PRI_USU ASC
        LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $search, $search, $search, $search, $search);
$stmt->execute();
$resultado = $stmt->get_result();

$res = [];
while ($fila = $resultado->fetch_assoc()) {
    $res[] = [
        "cedula" => $fila["ced"],
        "nombreCompleto" => $fila["nombres"] . " " . $fila["apellidos"],
        "correo" => $fila["correo"]
    ];
}

echo json_encode($res, JSON_UNESCAPED_UNICODE);
$stmt->close();
$conn->close();
?>
