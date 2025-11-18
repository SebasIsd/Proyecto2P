<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';


if (!isset($_SESSION['correo'])) {
    die('Debe iniciar sesión para descargar el certificado.');
}

$idIns = isset($_GET['id_ins']) ? (int)$_GET['id_ins'] : 0;
if ($idIns <= 0) {
    die('Inscripción inválida.');
}

// ==================================================
// Helper: conversión de texto UTF-8 → ISO-8859-1
// ==================================================
function pdf_text(string $txt): string {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt);
}

// ==================================================
// 1. Obtener datos de inscripción + usuario + evento
// ==================================================
$sql = "
    SELECT 
        i.ID_INS,
        i.CED_USU,
        i.ID_EVE_CUR,
        i.ESTADO_INS,
        i.EST_PAG_INS,
        i.CERT_GENERADO,
        i.CERT_FECHA_EMISION,
        i.RUTA_CERTIFICADO,
        
        u.NOM_PRI_USU,
        u.NOM_SEG_USU,
        u.APE_PRI_USU,
        u.APE_SEG_USU,
        
        e.TIT_EVE_CUR,
        e.DES_EVE_CUR,
        e.FEC_INI_EVE_CUR,
        e.FEC_FIN_EVE_CUR,
        e.HORAS_TOTALES,
        e.MOD_EVE_CUR,
        
        te.NOM_TIPO_EVE
    FROM INSCRIPCIONES i
    JOIN USUARIOS u       ON u.CED_USU = i.CED_USU
    JOIN EVENTOS_CURSOS e ON e.ID_EVE_CUR = i.ID_EVE_CUR
    JOIN TIPOS_EVENTO te  ON te.ID_TIPO_EVE = e.ID_TIPO_EVE
    WHERE i.ID_INS = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idIns);
$stmt->execute();
$ins = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ins) {
    die('Inscripción no encontrada.');
}

// 👇 Si quieres restringir que solo el dueño (no admin) lo descargue:
// $cedulaSesion = $_SESSION['cedula'] ?? null;
// if ($cedulaSesion !== $ins['CED_USU'] && strtolower($_SESSION['rol_nombre'] ?? '') !== 'administrador') {
//     die('No tiene permisos para este certificado.');
// }

// ==================================================
// 2. Validar que el evento haya finalizado
// ==================================================
$today   = new DateTime();
$endDate = new DateTime($ins['FEC_FIN_EVE_CUR']);

if ($today < $endDate) {
    die('El evento aún no ha finalizado. No se puede emitir el certificado.');
}

// ==================================================
// 3. Validar que la inscripción esté COMPLETADA
// ==================================================
if ($ins['ESTADO_INS'] !== 'Completado') {
    die('La inscripción no está marcada como Completada. No se puede emitir el certificado.');
}

// ==================================================
// 4. Validar pago si el evento es pagado
// ==================================================
if ($ins['MOD_EVE_CUR'] === 'Pagado') {
    $sqlPag = "SELECT COUNT(*) AS c FROM PAGOS WHERE ID_INS = ? AND ESTADO_VALIDACION = 'Aprobado'";
    $stmt = $conn->prepare($sqlPag);
    $stmt->bind_param("i", $idIns);
    $stmt->execute();
    $resPag = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int)$resPag['c'] === 0) {
        die('El pago correspondiente aún no ha sido aprobado. No se puede emitir el certificado.');
    }
}

// ==================================================
// 5. Validar evidencias obligatorias
// ==================================================
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
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalOblig = (int)($r['total_oblig'] ?? 0);
$totalAprob = (int)($r['total_aprob'] ?? 0);

if ($totalOblig > 0 && $totalOblig !== $totalAprob) {
    die('No se han cumplido todos los requisitos obligatorios. No se puede emitir el certificado.');
}

// ==================================================
// 6. Incluir FPDF
// ==================================================
require_once __DIR__ . '/../lib/fpdf186/fpdf.php';

// ==================================================
// 7. Clase PDF personalizada
// ==================================================
class PDFCert extends FPDF
{
    function Header()
    {
        // Logo (ajusta ruta y tamaño según tu proyecto)
        $logoPath = __DIR__ . '/../images/logo_uta.jpg';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 10, 30);
        }

        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, pdf_text('UNIVERSIDAD TÉCNICA DE AMBATO'), 0, 1, 'C');

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 8, pdf_text('Facultad de Ingeniería en Sistemas, Electrónica e Industrial'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, pdf_text('Emitido automáticamente por el sistema de eventos.'), 0, 1, 'C');
        $this->Cell(0, 5, pdf_text('Fecha de emisión: ') . date('Y-m-d'), 0, 0, 'C');
    }
}

// ==================================================
// 8. Preparar datos del certificado
// ==================================================
$nombreCompleto = trim(
    $ins['NOM_PRI_USU'] . ' ' .
    ($ins['NOM_SEG_USU'] ? $ins['NOM_SEG_USU'] . ' ' : '') .
    $ins['APE_PRI_USU'] . ' ' .
    ($ins['APE_SEG_USU'] ?? '')
);
$nombreCompleto = strtoupper($nombreCompleto);

$tituloEvento = $ins['TIT_EVE_CUR'];
$tipoEvento   = $ins['NOM_TIPO_EVE'];

$fechaIni = (new DateTime($ins['FEC_INI_EVE_CUR']))->format('d/m/Y');
$fechaFin = (new DateTime($ins['FEC_FIN_EVE_CUR']))->format('d/m/Y');
$horas    = (int)$ins['HORAS_TOTALES'];

// ==================================================
// 9. Crear PDF y contenido
// ==================================================
$pdf = new PDFCert('L', 'mm', 'A4'); // Horizontal, A4
$pdf->AddPage();

// Título "CERTIFICADO"
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(163, 0, 0);
$pdf->Cell(0, 20, pdf_text('CERTIFICADO'), 0, 1, 'C');
$pdf->Ln(6);

// Texto descriptivo
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 14);
$pdf->MultiCell(0, 8, pdf_text(
    "La Universidad Técnica de Ambato, a través de la Facultad de Ingeniería en Sistemas, Electrónica e Industrial,\n" .
    "otorga el presente certificado a:"
), 0, 'C');
$pdf->Ln(6);

// Nombre grande
$pdf->SetFont('Arial', 'B', 22);
$pdf->Cell(0, 12, pdf_text($nombreCompleto), 0, 1, 'C');
$pdf->Ln(4);

// Texto del evento
$pdf->SetFont('Arial', '', 14);
$textoEvento = "Por su participación en el $tipoEvento denominado:\n\"$tituloEvento\",\n" .
               "realizado del $fechaIni al $fechaFin, con una duración de $horas horas académicas.";
$pdf->MultiCell(0, 8, pdf_text($textoEvento), 0, 'C');
$pdf->Ln(15);

// Firmas
$pdf->SetFont('Arial', '', 12);
$yFirmas = $pdf->GetY() + 15;

$pdf->SetY($yFirmas);
$pdf->Cell(90, 6, pdf_text('___________________________'), 0, 0, 'C');
$pdf->Cell(90, 6, '', 0, 0, 'C');
$pdf->Cell(90, 6, pdf_text('___________________________'), 0, 1, 'C');

$pdf->Cell(90, 6, pdf_text('Director de Carrera'), 0, 0, 'C');
$pdf->Cell(90, 6, '', 0, 0, 'C');
$pdf->Cell(90, 6, pdf_text('Responsable del Evento'), 0, 1, 'C');

// ==================================================
// 10. Guardar el PDF en el servidor
// ==================================================
$dirCert = __DIR__ . '/../uploads/certificados/';

// Crear carpeta si no existe
if (!is_dir($dirCert)) {
    mkdir($dirCert, 0775, true);
}

// Nombre de archivo (puedes personalizarlo)
$archivo = 'cert_' . $idIns . '_' . time() . '.pdf';

// Ruta física
$rutaFisica  = $dirCert . $archivo;
// Ruta pública (para guardar en BD)
$rutaPublica = 'uploads/certificados/' . $archivo;

// Limpiar buffer por si acaso
if (ob_get_length()) {
    ob_end_clean();
}

// Guardar en disco
$pdf->Output('F', $rutaFisica);

// ==================================================
// 11. Actualizar INSCRIPCIONES con información del certificado
// ==================================================
$sqlCert = "
    UPDATE INSCRIPCIONES
    SET 
      CERT_GENERADO      = 1,
      CERT_FECHA_EMISION = IFNULL(CERT_FECHA_EMISION, NOW()),
      RUTA_CERTIFICADO   = ?
    WHERE ID_INS = ?
";
$stmt = $conn->prepare($sqlCert);
$stmt->bind_param("si", $rutaPublica, $idIns);
$stmt->execute();
$stmt->close();

// ==================================================
// 12. Enviar el PDF al navegador
// ==================================================
$pdf->Output('I', 'Certificado.pdf');
exit;
?>