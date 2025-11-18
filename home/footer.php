<?php
// home/footer.php
// Devuelve un array con los datos del footer.

include __DIR__ . '/../includes/conexion.php'; // ruta relativa

$data = [
    "telefono"  => "Información no disponible",
    "correo"    => "Información no disponible",
    "Des_logo"  => "Información no disponible",
    "face"      => "Información no disponible",
    "ins_gra"   => "Información no disponible",
    "dias"      => "Información no disponible",
    "horas"     => "Información no disponible",
    "derechos"  => "Información no disponible"
];

// Consulta a la tabla footer
$sql = "SELECT telefono, correo, Des_logo, face, ins_gra, dias, horas, derechos 
        FROM footer 
        LIMIT 1";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();

    $data = [
        "telefono"  => $row["telefono"]  ?? $data["telefono"],
        "correo"    => $row["correo"]    ?? $data["correo"],
        "Des_logo"  => $row["Des_logo"]  ?? $data["Des_logo"],
        "face"      => $row["face"]      ?? $data["face"],
        "ins_gra"   => $row["ins_gra"]   ?? $data["ins_gra"],
        "dias"      => $row["dias"]      ?? $data["dias"],
        "horas"     => $row["horas"]     ?? $data["horas"],
        "derechos"  => $row["derechos"]  ?? $data["derechos"]
    ];
}

// Devuelve el array sin imprimir nada
return $data;
