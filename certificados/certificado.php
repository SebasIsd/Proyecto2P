<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// 🔐 Opcional: restringir a usuario logueado
if (!isset($_SESSION['correo'])) {
    die('Debe iniciar sesión para descargar el certificado.');
}

// 🧩 Recibir ID de inscripción
$idIns = isset($_GET['id_ins']) ? (int)$_GET['id_ins'] : 0;
if ($idIns <= 0) {
    die('Inscripción inválida.');
}

// 👤 Si quieres que solo el dueño pueda descargarlo (no admin):
// $cedulaSesion = $_SESSION['cedula'] ?? null;

// 1. Obtener datos principales de inscripción, usuario y evento
$sql = "
    SELECT 
        i.ID_INS, i.CED_USU, i.ID_EVE_CUR, i.ESTADO_INS, i.EST_PAG_INS,
        u.NOM_PRI_USU, u.NOM_SEG_USU, u.APE_PRI_USU, u.APE_SEG_USU,
        e.TIT_EVE_CUR, e.DES_EVE_CUR, e.FEC_INI_EVE_CUR, e.FEC_FIN_EVE_CUR,
        e.HORAS_TOTALES, e.MOD_EVE_CUR, e.REQUIERE_ASISTENCIA,
        te.NOM_TIPO_EVE
    FROM INSCRIPCIONES i
    JOIN USUARIOS u      ON u.CED_USU = i.CED_USU
    JOIN EVENTOS_CURSOS e ON e.ID_EVE_CUR = i.ID_EVE_CUR
    JOIN TIPOS_EVENTO te  ON te.ID_TIPO_EVE = e.ID_TIPO_EVE
    WHERE i.ID_INS = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idIns);
$stmt->execute();
$res = $stmt->get_result();
$ins = $res->fetch_assoc();
$stmt->close();

if (!$ins) {
    die('Inscripción no encontrada.');
}

// Aquí podrías validar que solo el propio usuario descargue el certificado:
// if ($cedulaSesion !== $ins['CED_USU'] && strtolower($_SESSION['rol_nombre'] ?? '') !== 'administrador') {
//     die('No tiene permisos para este certificado.');
// }

// 2. Validar que el evento haya terminado
$hoy = new DateTime();
$finEvento = new DateTime($ins['FEC_FIN_EVE_CUR']);
if ($finEvento > $hoy) {
    die('El evento aún no ha finalizado. No se puede emitir el certificado.');
}

// 3. Validar estado de inscripción (puedes ajustar esta condición)
if ($ins['ESTADO_INS'] !== 'Completado') {
    die('La inscripción no está marcada como Completada. No se puede emitir el certificado.');
}

// 4. Validar pago (solo si el evento es pagado)
if ($ins['MOD_EVE_CUR'] === 'Pagado') {
    $sqlPag = "SELECT COUNT(*) AS c FROM PAGOS WHERE ID_INS = ? AND ESTADO_VALIDACION = 'Aprobado'";
    $stmt = $conn->prepare($sqlPag);
    $stmt->bind_param("i", $idIns);
    $stmt->execute();
    $resPag = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int)$resPag['c'] === 0) {
        die('No se ha aprobado el pago correspondiente. No se puede emitir el certificado.');
    }
}

// 5. Validar evidencias de TODOS los requisitos obligatorios
$sqlReq = "
    SELECT 
        COUNT(*) AS total_oblig,
        SUM(
            CASE 
              WHEN ev.ESTADO_VALIDACION = 'Aprobado'
                   AND (
                        er.VALOR_MINIMO_APROBATORIO IS NULL
                        OR ev.VALOR_NUMERICO IS NULL
                        OR ev.VALOR_NUMERICO >= er.VALOR_MINIMO_APROBATORIO
                   )
              THEN 1 ELSE 0 
            END
        ) AS total_aprob
    FROM EVENTOS_REQUISITOS er
    LEFT JOIN EVIDENCIAS ev
      ON ev.ID_REQ = er.ID_REQ
     AND ev.ID_INS = ?
    WHERE er.ID_EVE_CUR = ?
      AND er.OBLIGATORIO = 1
";
$stmt = $conn->prepare($sqlReq);
$stmt->bind_param("ii", $idIns, $ins['ID_EVE_CUR']);
$stmt->execute();
$reqRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalOblig = (int)($reqRow['total_oblig'] ?? 0);
$totalAprob = (int)($reqRow['total_aprob'] ?? 0);

if ($totalOblig > 0 && $totalOblig !== $totalAprob) {
    die('No se han cumplido todos los requisitos obligatorios. No se puede emitir el certificado.');
}

// 6. (Opcional) Validar asistencia si REQUIERE_ASISTENCIA = 1
// Aquí iría la lógica si tuvieras tabla de asistencia.
// Por ahora, asumimos que está OK.
// if ($ins['REQUIERE_ASISTENCIA']) { ... }

// ============================
// 7. Generar PDF con FPDF
// ============================
require_once __DIR__ . '/../lib/fpdf/fpdf.php'; // AJUSTA ESTA RUTA A TU INSTALACIÓN

class PDFCertificado extends FPDF
{
    function Header()
    {
        // Logo UTA (ajusta ruta)
        $this->Image(__DIR__ . '/../images/logo_uta.png', 15, 10, 30);
        // Título superior
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('UNIVERSIDAD TÉCNICA DE AMBATO'), 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 8, utf8_decode('Facultad de Ingeniería en Sistemas, Electrónica e Industrial'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, utf8_decode('Emitido automáticamente por el sistema de eventos.'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('Fecha de emisión: ') . date('Y-m-d'), 0, 0, 'C');
    }
}

$pdf = new PDFCertificado('L', 'mm', 'A4'); // Horizontal, A4
$pdf->AddPage();

// Datos útiles
$nombreCompleto = trim(
    $ins['NOM_PRI_USU'] . ' ' .
    ($ins['NOM_SEG_USU'] ? $ins['NOM_SEG_USU'] . ' ' : '') .
    $ins['APE_PRI_USU'] . ' ' .
    ($ins['APE_SEG_USU'] ?? '')
);
$nombreCompleto = strtoupper($nombreCompleto);
$tituloEvento  = $ins['TIT_EVE_CUR'];
$tipoEvento    = $ins['NOM_TIPO_EVE'];
$fechaIni      = (new DateTime($ins['FEC_INI_EVE_CUR']))->format('d/m/Y');
$fechaFin      = (new DateTime($ins['FEC_FIN_EVE_CUR']))->format('d/m/Y');
$horas         = (int)$ins['HORAS_TOTALES'];

// Título "CERTIFICADO"
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(163, 0, 0);
$pdf->Cell(0, 20, utf8_decode('CERTIFICADO'), 0, 1, 'C');
$pdf->Ln(5);

// Texto principal
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 14);
$pdf->MultiCell(0, 8, utf8_decode(
    "La Universidad Técnica de Ambato, a través de la Facultad de Ingeniería en Sistemas, Electrónica e Industrial,\n" .
    "otorga el presente certificado a:"
), 0, 'C');
$pdf->Ln(8);

// Nombre grande
$pdf->SetFont('Arial', 'B', 22);
$pdf->Cell(0, 12, utf8_decode($nombreCompleto), 0, 1, 'C');
$pdf->Ln(5);

// Texto del evento
$pdf->SetFont('Arial', '', 14);
$textoEvento = "Por su participación en el $tipoEvento denominado:\n\"$tituloEvento\",\n" .
               "realizado del $fechaIni al $fechaFin, con una duración de $horas horas académicas.";
$pdf->MultiCell(0, 8, utf8_decode($textoEvento), 0, 'C');
$pdf->Ln(15);

// Firmas (decorativo, puedes ajustar)
$pdf->SetFont('Arial', '', 12);
$yFirmas = $pdf->GetY() + 15;

$pdf->SetY($yFirmas);
$pdf->Cell(90, 6, utf8_decode('___________________________'), 0, 0, 'C');
$pdf->Cell(90, 6, '', 0, 0, 'C');
$pdf->Cell(90, 6, utf8_decode('___________________________'), 0, 1, 'C');

$pdf->Cell(90, 6, utf8_decode('Director de Carrera'), 0, 0, 'C');
$pdf->Cell(90, 6, '', 0, 0, 'C');
$pdf->Cell(90, 6, utf8_decode('Responsable del Evento'), 0, 1, 'C');

// Salida
$nombreArchivo = 'Certificado_' . preg_replace('/\s+/', '_', $nombreCompleto) . '.pdf';
$pdf->Output('I', $nombreArchivo);
exit;
?>