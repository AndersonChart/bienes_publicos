<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/../bd/conexion.php";

//CLASE DEL PDF

class PDF_MC_Table extends FPDF {

    function Header() {

        // MEMBRETE
        $this->Image('../img/icons/membrete.png', 10, 8, 190);

        // LOGO
        $this->Image('../img/icons/logouni.png', 10, 30, 20);

        $this->Ln(10);
        

        // TÍTULO PRINCIPAL
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 50, utf8_decode('SISTEMA DE CONTROL DE INVENTARIO'), 0, 1, 'C');

        // LÍNEA
        $this->Ln(3);
        $this->SetDrawColor(0, 0, 0);
        $this->Line(10, 28, 200, 28);

        $this->Ln(0);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Generado automáticamente · Página ') . $this->PageNo(), 0, 0, 'C');
    }
}


//CONSULTA PRINCIPAL DEL INVENTARIO
   

$conexion = Conexion::conectar();

$sql = "
    SELECT 
        a.articulo_codigo,
        a.articulo_nombre,
        c.categoria_nombre,
        cl.clasificacion_nombre,
        a.articulo_modelo,
        m.marca_nombre,
        a.articulo_estado
    FROM articulo a
    INNER JOIN clasificacion cl ON cl.clasificacion_id = a.clasificacion_id
    INNER JOIN categoria c ON c.categoria_id = cl.categoria_id
    LEFT JOIN marca m ON m.marca_id = a.marca_id
    ORDER BY a.articulo_codigo ASC
";

$consulta = $conexion->query($sql);
$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);


//GENERACIÓN DEL PDF
   

$pdf = new PDF_MC_Table('P','mm','A4');
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);
$pdf->SetFont('Arial','',9);

//ENCABEZADO SUPERIOR
$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell(0,6,utf8_decode('UNES/DTIT/'). date('Y/_________'), 0,1,'R');
$pdf->Ln(2);

//TEXTO DESCRIPTIVO
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "      En cumplimiento de los procesos de control y administración de bienes públicos, "
    . "se expone el inventario institucional actualizado. Este informe tiene como finalidad "
    . "garantizar la transparencia en la gestión patrimonial, respaldando las labores de "
    . "supervisión, mantenimiento y resguardo de los activos bajo responsabilidad del ente."
));
$pdf->Ln(5);


// CABECERA TABLA
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(0, 102, 204);

$pdf->Cell(20,7,utf8_decode('CÓDIGO'),1,0,'C',true);
$pdf->Cell(40,7,utf8_decode('NOMBRE'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('CATEGORÍA'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('CLASIFICACIÓN'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('MODELO'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('MARCA'),1,1,'C',true);


// FILAS
$pdf->SetFont('Arial','',8);
$fill = false;

foreach ($filas as $fila) {
    $pdf->SetFillColor($fill ? 255 : 255);

    $pdf->Cell(20,7,utf8_decode($fila['articulo_codigo']),1,0,'C',true);
    $pdf->Cell(40,7,utf8_decode($fila['articulo_nombre']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['categoria_nombre']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['clasificacion_nombre']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['articulo_modelo']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['marca_nombre']),1,1,'L',true);

    $fill = !$fill;
}


//CONTROL DE ESPACIO PARA CONCLUSIÓN + FIRMAS
   

$espacioNecesario = 50;

if ($pdf->GetY() + $espacioNecesario > 260) {
    $pdf->AddPage();
}


//CONCLUSIÓN
   

$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "     Sin más que agregar, se deja constancia del presente inventario institucional, "
    . "validado por la Dirección y la Delegación de Bienes Nacionales."
));


//FIRMAS
   

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
$pdf->Output("I","Reporte-Inventario.pdf");
exit;

?>
