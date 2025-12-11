<?php
require_once '../../includes/conexion.php';

// SOLO eventos que están en favoritos_eventos
$sql = "
    SELECT 
        e.ID_EVE_CUR,
        e.TIT_EVE_CUR,
        e.DES_EVE_CUR,
        CONCAT(e.FEC_INI_EVE_CUR, ' - ', e.FEC_FIN_EVE_CUR) AS fecha,
        ec.ID_CARRERA,
        e.IMG_EVE_CUR,
        1 AS favorito  -- Todos los que vienen aquí son favoritos
    FROM eventos_favoritos f
    INNER JOIN eventos_cursos e 
        ON e.ID_EVE_CUR = f.ID_EVE_CUR
    LEFT JOIN eventos_carreras ec
        ON ec.ID_EVE_CUR = e.ID_EVE_CUR
";

$result = $conn->query($sql);

$eventos = [];

while ($fila = $result->fetch_assoc()) {
    $eventos[] = [
        "id"          => $fila["ID_EVE_CUR"],
        "nombre"      => $fila["TIT_EVE_CUR"],
        "descripcion" => $fila["DES_EVE_CUR"],
        "fecha"       => $fila["fecha"],
        "imagen"      => $fila["IMG_EVE_CUR"],
        "id_carrera"  => $fila["ID_CARRERA"] ?? 0,
        "favorito"    => 1  // Siempre favorito
    ];
}

header('Content-Type: application/json');
echo json_encode($eventos);
?>
