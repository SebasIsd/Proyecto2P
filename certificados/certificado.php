<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';
// ⚠️ Nota: Asegúrate de tener el archivo fpdf.php en la ruta correcta: __DIR__ . '/../lib/fpdf186/fpdf.php'
require_once __DIR__ . '/../lib/fpdf186/fpdf.php';


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
    // Si la cadena está vacía, devuelve una cadena vacía para evitar errores de iconv
    if (empty($txt)) {
        return '';
    }
    // Añadida la opción //IGNORE para evitar problemas con caracteres muy raros
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $txt);
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
// 7. Clase PDF personalizada (sin Header automático)
// ==================================================
class PDFCert extends FPDF
{
    // Colores personalizados
    public $colorUtaRed    = [163, 0, 0];   // Rojo UTA
    public $colorUtaBlue   = [0, 51, 102];  // Azul Oscuro (acento profesional)
    public $colorText      = [51, 51, 51];  // Gris oscuro para el cuerpo de texto
    public $colorWhite     = [171, 143, 24]; // Blanco para contraste

    // Desactivar el header automático
    function Header()
    {
        // Dejamos vacío para dibujar manualmente
    }

    function Footer()
    {
        $pageWidth = $this->GetPageWidth();
        $this->SetY(-25); // 25mm desde el final
        $this->SetTextColor(150, 150, 150); // Gris claro

        // ID de Validación (Simulado)
        $idIns = isset($_GET['id_ins']) ? (int)$_GET['id_ins'] : 0;
        $validationCode = str_pad($idIns, 6, "0", STR_PAD_LEFT) . "-" . date('Y');
        $dateEmitted = date('d-m-Y');

        // Fila 1: ID de Validación (Izquierda)
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, pdf_text('Código de Validación: ' ) . $validationCode, 0, 1, 'C');
        
        // Fila 1: Fecha de emisión (Derecha)
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5 , pdf_text('Fecha de emisión: ') . $dateEmitted, 0, 1, 'C');

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

// Formato de fecha
$fechaIni = (new DateTime($ins['FEC_INI_EVE_CUR']))->format('d/m/Y');
$fechaFin = (new DateTime($ins['FEC_FIN_EVE_CUR']))->format('d/m/Y');
$horas    = (int)$ins['HORAS_TOTALES'];

// ==================================================
// 9. Crear PDF y contenido (Cuerpo del Certificado)
// ==================================================
$pdf = new PDFCert('L', 'mm', 'A4'); // Horizontal, A4
$pdf->AddPage();

// --- PRIMERO: Dibujar la imagen de fondo ---
$backgroundImage = __DIR__ . '/../images/fondo_certificado.jpg'; // Ajusta la ruta a tu imagen
if (file_exists($backgroundImage)) {
    $pageWidth = $pdf->GetPageWidth();
    $pageHeight = $pdf->GetPageHeight();
    
    // Dibujar la imagen de fondo (cubre toda la página)
    $pdf->Image($backgroundImage, 0, 0, $pageWidth, $pageHeight, '', '', '', false, 100);
}

// --- SEGUNDO: Añadir las fuentes Amita y Charm ---
// Asegúrate de que los archivos Amita-Bold.php y Charm-Regular.php están en lib/fpdf186/
$pdf->AddFont('Amita','B','Amita-Bold.php');
$pdf->AddFont('Charm','R','Charm-Regular.php');

// --- TERCERO: Dibujar manualmente el encabezado (Logo + Texto Institucional) ---
$pageWidth = $pdf->GetPageWidth();
$logoPath = __DIR__ . '/../images/logoUTA.png';

// 1. Logo
$logoWidth = 40;
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 15, 15, $logoWidth); 
}

// 2. Títulos de la Institución (usamos blanco para contraste)
$pdf->SetY(15);
$pdf->SetX(15 + $logoWidth + 5); 
$pdf->SetTextColor($pdf->colorWhite[0], $pdf->colorWhite[1], $pdf->colorWhite[2]); // Blanco para que se vea sobre cualquier fondo

$pdf->SetFont('Amita','B',18); // Usamos Amita-Bold para el nombre de la universidad
$pdf->Cell($pageWidth - (15 + $logoWidth + 5 + 15), 10, pdf_text('UNIVERSIDAD TÉCNICA DE AMBATO'), 0, 1, 'R'); 

// 3. Título de la Facultad (usamos blanco también)
$pdf->SetX(15 + $logoWidth + 5);
$pdf->SetFont('Charm','R',12); // Usamos Charm-Regular para la facultad
$pdf->Cell($pageWidth - (15 + $logoWidth + 5 + 15), 6, pdf_text('Facultad de Ingeniería en Sistemas, Electrónica e Industrial'), 0, 1, 'R');

// 4. Línea divisoria (más corta y centrada bajo la facultad)
$lineX1 = $pageWidth - 80; // Inicia a 80mm del borde derecho
$lineX2 = $pageWidth - 15; // Termina a 15mm del borde derecho
$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor($pdf->colorUtaRed[0], $pdf->colorUtaRed[1], $pdf->colorUtaRed[2]);
$pdf->Line($lineX1, 32, $lineX2, 32);

$pdf->Ln(10); // Salto de línea después del encabezado

// --- CUARTO: Dibujar el contenido principal ---

// Título "CERTIFICADO DE PARTICIPACIÓN"
$pdf->SetY(50); // Mueve el contenido abajo para dejar espacio al encabezado
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Amita','B',20); // Usamos Amita-Bold para el título
$pdf->Cell(0, 10, pdf_text('CERTIFICADO DE PARTICIPACIÓN'), 0, 1, 'C');
$pdf->Ln(5);

// Texto de Concesión (Preámbulo)
$pdf->SetTextColor(51, 51, 51); // Gris Oscuro
$pdf->SetFont('Charm','R',14); // Usamos Charm-Regular para el texto
// Ajuste de texto para mejor espaciado
$pdf->MultiCell(0, 7, pdf_text(
    "La Universidad Técnica de Ambato, a través de la Facultad de Ingeniería en Sistemas, Electrónica e Industrial," .
    " otorga el presente reconocimiento por haber cumplido satisfactoriamente con los requisitos, a:"
), 0, 'C');
$pdf->Ln(8); // Aumentado el espacio antes del nombre

// Nombre del Participante (El más grande)
$pdf->SetFont('Amita','B',32); // Usamos Amita-Bold para el nombre
$pdf->SetTextColor($pdf->colorUtaRed[0], $pdf->colorUtaRed[1], $pdf->colorUtaRed[2]);
// Se usa MultiCell para centrar el texto correctamente incluso si el nombre es largo
$pdf->MultiCell(0, 16, pdf_text($nombreCompleto), 0, 'C');
$pdf->Ln(10);

// Texto de valiosa participación
$pdf->SetTextColor(51, 51, 51); // Gris Oscuro
$pdf->SetFont('Charm','R',14); // Usamos Charm-Regular
$pdf->MultiCell(0, 7, pdf_text(
    "Por su valiosa participación en el $tipoEvento"
), 0, 'C');
$pdf->Ln(2);

// Título del evento (Destacado)
$pdf->SetFont('Amita','B',18); // Usamos Amita-Bold para el título del evento
$pdf->SetTextColor($pdf->colorUtaBlue[0], $pdf->colorUtaBlue[1], $pdf->colorUtaBlue[2]);
$pdf->MultiCell(0, 9, pdf_text(
    "\"$tituloEvento\""
), 0, 'C');
$pdf->Ln(5);

// Duración y Fechas
$pdf->SetTextColor(51, 51, 51); // Gris Oscuro
$pdf->SetFont('Charm','R',14); // Usamos Charm-Regular
// Si la fecha inicial es igual a la final, se muestra solo una fecha.
$rangoFechas = $fechaIni === $fechaFin 
             ? "realizado el $fechaIni" 
             : "realizado del $fechaIni al $fechaFin";

$textoDuracion = "Evento $rangoFechas, con una duración certificada de $horas horas académicas.";

$pdf->MultiCell(0, 8, pdf_text($textoDuracion), 0, 'C');
$pdf->Ln(20); // Más espacio antes de las firmas


// Firmas (Mejor distribución con solo 2 columnas)
$pdf->SetFont('Arial', 'B', 12);
$yFirmas = $pdf->GetY() + 5; // Posición Y para las líneas de firma

$wFirma = 80; // Ancho de la línea de firma
$sepFirma = 50; // Separación entre firmas (aumentada de 20 a 50)
$totalAncho = (2 * $wFirma) + $sepFirma;

// Calcular el punto de inicio para centrar las dos firmas
$xStart = ($pdf->GetPageWidth() - $totalAncho) / 2;

// --- Firma 1: Director de Carrera ---
$pdf->SetX($xStart);
$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(0, 0, 0); 
$pdf->Cell($wFirma, 0, '', 'B', 0, 'C'); // Línea de firma (Borde inferior)
$pdf->Cell($sepFirma, 0, '', 0, 0, 'C'); // Espacio de separación

// --- Firma 2: Responsable del Evento ---
$pdf->Cell($wFirma, 0, '', 'B', 1, 'C'); // Línea de firma y salto de línea

$pdf->Ln(2); // Espacio entre línea y texto

// Títulos de Firmantes
$pdf->SetTextColor(51, 51, 51);
$pdf->SetFont('Arial', '', 10);
$pdf->SetX($xStart);
$pdf->Cell($wFirma, 6, pdf_text('Director de Carrera'), 0, 0, 'C');
$pdf->Cell($sepFirma, 6, '', 0, 0, 'C'); 
$pdf->Cell($wFirma, 6, pdf_text('Responsable del Evento'), 0, 1, 'C');


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