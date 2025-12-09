<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/../bd/conexion.php"; 


class PDF_MC_Table extends FPDF {

    function Header() {

        // MEMBRETE
        $this->Image('../img/icons/membrete.png', 10, 8, 190);

        $this->Image('../img/icons/logouni.png', 10, 30, 20);
        

        // TÍTULO PRINCIPAL
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 50, utf8_decode('SISTEMA DE CONTROL DE USUARIO'), 0, 1, 'C');

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



$conexion = Conexion::conectar(); // PDO

$pdf = new PDF_MC_Table('P','mm','A4');
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

// DESARROLLO
$pdf->Ln(4);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "     El presente reporte reúne el listado actualizado de los usuarios registrados en el Sistema de Asignación de Bienes Públicos, incluyendo sus datos identificativos, roles y condiciones de acceso. " .
    "Esta información permite garantizar la trazabilidad, transparencia y correcto control de los permisos otorgados dentro de la plataforma, asegurando que cada usuario opere conforme a sus responsabilidades dentro de la institución."
));
$pdf->Ln(6);

// CONSULTA DE USUARIOS
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

// ENCABEZADOS DE TABLA
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(0, 102, 204);

$pdf->Cell(35,7,utf8_decode('NOMBRE'),1,0,'C',true);
$pdf->Cell(35,7,utf8_decode('APELLIDO'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('USUARIO'),1,0,'C',true);
$pdf->Cell(60,7,utf8_decode('CORREO'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('ROL'),1,1,'C',true);

// FILAS
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);

foreach($filas as $fila) {
    
    $pdf->Cell(35,7,utf8_decode($fila['usuario_nombre']),1,0,'L');
    $pdf->Cell(35,7,utf8_decode($fila['usuario_apellido']),1,0,'L');
    $pdf->Cell(30,7,utf8_decode($fila['usuario_usuario']),1,0,'L');
    $pdf->Cell(60,7,utf8_decode($fila['usuario_correo']),1,0,'L');
    $pdf->Cell(30,7,utf8_decode($fila['rol_nombre']),1,1,'L');
}

//CONTROL DE ESPACIO PARA CONCLUSIÓN + FIRMAS
// Espacio mínimo requerido para concluir bien el reporte
$espacioNecesario = 50; // mm → suficiente para conclusión + firmas

// Si no queda espacio suficiente en la página actual:
if ($pdf->GetY() + $espacioNecesario > 260) {  
    $pdf->AddPage();
}

// CONCLUSIÓN
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "     Sin más que agregar, se deja constancia del presente listado de usuarios institucionales, " .
    "validado por la Dirección y la Coordinación del área correspondiente."
));

// FIRMAS
$pdf->Ln(30);
$pdf->Cell(80,6,'______________________________',0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,'______________________________',0,1,'C');

$pdf->SetFont('Arial','B',9);
$pdf->Cell(80,6,utf8_decode('Ing. Anirexis Gómez'),0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,utf8_decode('Ing. Walter Romero'),0,1,'C');

$pdf->SetFont('Arial','',9);
$pdf->Cell(80,6,utf8_decode('Directora Nacional de Tecnología'),0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,utf8_decode('Coordinador de Desarrollo de Sistema'),0,1,'C');

// SALIDA
$pdf->Output("I","Reporte_Usuarios.pdf");
exit;
