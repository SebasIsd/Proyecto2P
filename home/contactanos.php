<?php
// home/contactanos.php
// Devuelve un array con los datos de la tabla contactanos.

include __DIR__ . '/../includes/conexion.php'; // ruta relativa

$data = [
    "id"        => "Información no disponible",
    "des_noso"  => "Información no disponible",
    "link_noso" => "Información no disponible"
];

// Consulta a la tabla contactanos
$sql = "SELECT id, des_noso, link_noso 
        FROM contactanos
        LIMIT 1";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();

    $data = [
        "id"        => $row["id"]        ?? $data["id"],
        "des_noso"  => $row["des_noso"]  ?? $data["des_noso"],
        "link_noso" => $row["link_noso"] ?? $data["link_noso"]
    ];
}

// Devuelve el array sin imprimir nada
return $data;
