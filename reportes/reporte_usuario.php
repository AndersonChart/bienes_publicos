<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/plantilla_reportes.php";
require_once __DIR__ . "/../bd/conexion.php"; 

$conexion = Conexion::conectar(); // PDO

$pdf = new PDF_MC_Table();
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

$sql = "
    SELECT 
        u.usuario_nombre,
        u.usuario_apellido,
        u.usuario_usuario,
        u.usuario_correo,
        r.rol_nombre,
        u.usuario_estado
    FROM usuario u
    INNER JOIN rol r ON r.rol_id = u.rol_id
    ORDER BY u.usuario_nombre ASC
";

$consulta = $conexion->query($sql);
$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);

// ENCABEZADOS
$pdf->SetFillColor(0, 102, 204); // azul de fondo
$pdf->SetTextColor(255, 255, 255); // texto blanco
$pdf->Cell(35,7,'Nombre',1,0,'C',true);
$pdf->Cell(35,7,'Apellido',1,0,'C',true);
$pdf->Cell(30,7,'Usuario',1,0,'C',true);
$pdf->Cell(60,7,'Correo',1,0,'C',true);
$pdf->Cell(30,7,'Rol',1,1,'C',true);

$pdf->SetFont('Arial','',8);

// Reset color de texto para filas
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',8);

foreach($filas as $fila) {
    $pdf->Cell(35,7,utf8_decode($fila['usuario_nombre']),1,0,'L');
    $pdf->Cell(35,7,utf8_decode($fila['usuario_apellido']),1,0,'L');
    $pdf->Cell(30,7,utf8_decode($fila['usuario_usuario']),1,0,'L');
    $pdf->Cell(60,7,utf8_decode($fila['usuario_correo']),1,0,'L');
    $pdf->Cell(30,7,utf8_decode($fila['rol_nombre']),1,1,'L');
    
}

// ---------------------- CONCLUSIÓN ------------------------
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "Sin más que agregar, se deja constancia del presente listado de usuario institucional, " .
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
$pdf->Cell(80,6,utf8_decode('Ing. Walter Romero'),0,1,'C');

$pdf->SetFont('Arial','',9);
$pdf->Cell(80,6,'Directora Nacional de Tecnologia',0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,'Coordinador de Desarrollo de Sistema',0,1,'C');


// ---------------------- SALIDA ------------------------
$pdf->Output("I","Reporte_inventario.pdf");

$pdf->Output("I","Reporte_Usuarios.pdf");
