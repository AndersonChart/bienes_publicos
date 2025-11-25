<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/plantilla_reportes.php";
require_once __DIR__ . "/../bd/conexion.php"; 

$conexion = Conexion::conectar(); // PDO

$pdf = new PDF_MC_Table();
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

// ---------------------- CONSULTA ---------------------------
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
    ORDER BY a.articulo_nombre ASC
";

$consulta = $conexion->query($sql);
$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);

// ---------------------- ENCABEZADOS ------------------------
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(20,7,'Codigo',1,0,'C',true);
$pdf->Cell(40,7,'Nombre',1,0,'C',true);
$pdf->Cell(30,7,'Categoria',1,0,'C',true);
$pdf->Cell(30,7,'Clasificacion',1,0,'C',true);
$pdf->Cell(30,7,'Modelo',1,0,'C',true);
$pdf->Cell(30,7,'Marca',1,1,'C',true);

// Reset colores
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',8);

// ---------------------- FILAS ------------------------
$fill = false;
foreach($filas as $fila) {
    $pdf->SetFillColor($fill ? 230 : 255);
    $pdf->Cell(20,7,utf8_decode($fila['articulo_codigo']),1,0,'C',true);
    $pdf->Cell(40,7,utf8_decode($fila['articulo_nombre']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['categoria_nombre']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['clasificacion_nombre']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['articulo_modelo']),1,0,'L',true);
    $pdf->Cell(30,7,utf8_decode($fila['marca_nombre']),1,1,'L',true);

    $fill = !$fill;
}

// ---------------------- CONCLUSIÓN ------------------------
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "Sin más que agregar, se deja constancia del presente inventario institucional, " .
    "validado por la Dirección y la Delegación de Bienes Nacionales."
));

// ---------------------- FIRMAS ------------------------
$pdf->Ln(20);

// Firma 1: Directora
$pdf->Cell(80,6,'______________________________',0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,'______________________________',0,1,'C');

$pdf->SetFont('Arial','B',9);
$pdf->Cell(80,6,utf8_decode('Ing. Anirexis Gomez'),0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,utf8_decode('Lcda. Dayanis Arellanos'),0,1,'C');

$pdf->SetFont('Arial','',9);
$pdf->Cell(80,6,'Directora',0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,'Delegada de Bienes Publicos',0,1,'C');


// ---------------------- SALIDA ------------------------
$pdf->Output("I","Reporte_inventario.pdf");

