<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/../bd/conexion.php";

class PDF_MC_Table extends FPDF {

    function Header() {
        $this->Image('../img/icons/membrete.png', 10, 8, 190);
        $this->Image('../img/icons/logouni.png', 10, 30, 20);
        $this->Ln(10);

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 50, utf8_decode('SISTEMA DE CONTROL DE INVENTARIO'), 0, 1, 'C');

        $this->Ln(3);
        $this->Line(10, 28, 200, 28);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Generado automáticamente · Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// ---------------- CONEXIÓN ----------------
$conexion = Conexion::conectar();

// ---------------- FILTROS ----------------
$where = [];
$params = [];

if (!empty($_GET['estado_articulo'])) {
    $where[] = "a.articulo_estado = ?";
    $params[] = (int)$_GET['estado_articulo'];
}

if (!empty($_GET['categoria_id'])) {
    $where[] = "c.categoria_id = ?";
    $params[] = (int)$_GET['categoria_id'];
}

if (!empty($_GET['clasificacion_id'])) {
    $where[] = "cl.clasificacion_id = ?";
    $params[] = (int)$_GET['clasificacion_id'];
}

// Filtro por estado de stock
if (!empty($_GET['estado_stock'])) {
    if ($_GET['estado_stock'] == 1) {
        $where[] = "s.estado_id = 1";
    } elseif ($_GET['estado_stock'] == 2) {
        $where[] = "s.estado_id = 2";
    } elseif ($_GET['estado_stock'] == 3) {
        $where[] = "s.estado_id = 3";
    }
}

$whereSQL = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// ---------------- CONSULTA ----------------
$sql = "
SELECT DISTINCT
    a.articulo_codigo,
    a.articulo_nombre,
    c.categoria_nombre,
    cl.clasificacion_nombre,
    a.articulo_modelo,
    m.marca_nombre
FROM articulo a
INNER JOIN clasificacion cl ON cl.clasificacion_id = a.clasificacion_id
INNER JOIN categoria c ON c.categoria_id = cl.categoria_id
LEFT JOIN marca m ON m.marca_id = a.marca_id
LEFT JOIN articulo_serial s ON s.articulo_id = a.articulo_id
$whereSQL
ORDER BY a.articulo_codigo ASC
";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------- PDF ----------------
$pdf = new PDF_MC_Table('P','mm','A4');
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

// Encabezado superior
$pdf->Cell(0,6,utf8_decode('UNES/DTIT/' . date('Y')), 0, 1,'R');
$pdf->Ln(3);

//TEXTO DESCRIPTIVO
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "      En cumplimiento de los procesos de control y administración de bienes públicos, "
    . "se expone el inventario institucional actualizado. Este informe tiene como finalidad "
    . "garantizar la transparencia en la gestión patrimonial, respaldando las labores de "
    . "supervisión, mantenimiento y resguardo de los activos bajo responsabilidad del ente."
));
$pdf->Ln(5);

// Cabecera tabla
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(0,102,204);


$pdf->Cell(15,7,utf8_decode('CÓDIGO'),1,0,'C',true);
$pdf->Cell(53,7,utf8_decode('NOMBRE'),1,0,'C',true);
$pdf->Cell(35,7,utf8_decode('CATEGORÍA'),1,0,'C',true);
$pdf->Cell(35,7,utf8_decode('CLASIFICACIÓN'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('MODELO'),1,0,'C',true);
$pdf->Cell(23,7,utf8_decode('MARCA'),1,1,'C',true);

// Filas
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);

foreach ($filas as $fila) {
    $pdf->Cell(15,7,$fila['articulo_codigo'],1);
    $pdf->Cell(53,7,utf8_decode($fila['articulo_nombre']),1);
    $pdf->Cell(35,7,utf8_decode($fila['categoria_nombre']),1);
    $pdf->Cell(35,7,utf8_decode($fila['clasificacion_nombre']),1);
    $pdf->Cell(30,7,utf8_decode($fila['articulo_modelo']),1);
    $pdf->Cell(23,7,utf8_decode($fila['marca_nombre']),1,1);
}

/* ============================================================
   CONTROL DE ESPACIO PARA CONCLUSIÓN + FIRMAS
   ============================================================ */

$espacioNecesario = 50;

if ($pdf->GetY() + $espacioNecesario > 260) {
    $pdf->AddPage();
}


/* ============================================================
   CONCLUSIÓN
   ============================================================ */

$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "     Sin más que agregar, se deja constancia del presente inventario institucional, "
    . "validado por la Dirección de Tecnología y la Delegación de Bienes Nacionales."
));


/* ============================================================
   FIRMAS
   ============================================================ */

$pdf->Ln(50);

$pdf->Cell(80,6,'______________________________',0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,'______________________________',0,1,'C');

$pdf->SetFont('Arial','B',9);
$pdf->Cell(80,6,utf8_decode('Ing. Anirexis Gómez'),0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,utf8_decode('Lcda. Dayanis Arellanos'),0,1,'C');

$pdf->SetFont('Arial','',9);
$pdf->Cell(80,6,utf8_decode('Directora Nacional de Tecnología'),0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,utf8_decode('Delegada de Bienes Públicos'),0,1,'C');



// SALIDA PDF
$pdf->Output("I","Reporte_inventario.pdf");
exit;

?>
