<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/../bd/conexion.php";

class PDF_MC_Table extends FPDF {

    function Header() {
        $this->Image('../img/icons/membrete.png', 10, 8, 190);
        $this->Image('../img/icons/logouni.png', 10, 30, 20);
        $this->Ln(10);

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 50, utf8_decode('SISTEMA DE CONTROL DE ASIGNACIÓN'), 0, 1, 'C');

        $this->Ln(3);
        $this->Line(10, 28, 200, 28);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(
            0,
            10,
            utf8_decode('Generado automáticamente · Página ') . $this->PageNo(),
            0,
            0,
            'C'
        );
    }
}

// ---------------- CONEXIÓN ----------------
$conexion = Conexion::conectar();

// ---------------- FILTROS ----------------
$estado     = $_GET['estado'] ?? '';
$cargo_id   = $_GET['cargo_id'] ?? '';
$persona_id = $_GET['persona_id'] ?? '';
$area_id    = $_GET['area_id'] ?? '';

$where = [];
$params = [];

if ($estado !== '') {
    $where[] = "a.asignacion_estado = ?";
    $params[] = $estado;
}

if ($cargo_id !== '') {
    $where[] = "c.cargo_id = ?";
    $params[] = $cargo_id;
}

if ($persona_id !== '') {
    $where[] = "p.persona_id = ?";
    $params[] = $persona_id;
}

if ($area_id !== '') {
    $where[] = "ar.area_id = ?";
    $params[] = $area_id;
}

$whereSQL = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// ---------------- CONSULTA ----------------
$sql = "
SELECT 
    a.asignacion_id,
    a.asignacion_fecha,
    a.asignacion_fecha_fin,
    a.asignacion_estado,
    p.persona_nombre,
    p.persona_apellido,
    c.cargo_nombre,
    ar.area_nombre
FROM asignacion a
INNER JOIN persona p ON p.persona_id = a.persona_id
INNER JOIN cargo c ON c.cargo_id = p.cargo_id
INNER JOIN area ar ON ar.area_id = a.area_id
$whereSQL
ORDER BY a.asignacion_fecha ASC
";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------- PDF ----------------
$pdf = new PDF_MC_Table('P','mm','A4');
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

// ENCABEZADO SUPERIOR
$pdf->Cell(0,6,utf8_decode('UNES/DTIT/' . date('Y')), 0, 1,'R');
$pdf->Ln(3);

// TEXTO DESCRIPTIVO (ADAPTADO)
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "      En cumplimiento de los procesos de control y administración de bienes públicos, "
  . "se presenta el registro general de asignaciones institucionales, el cual permite "
  . "verificar la correcta entrega, uso y custodia de los bienes asignados al personal "
  . "adscrito a las distintas áreas de la dirección, garantizando la transparencia "
  . "y trazabilidad en la gestión patrimonial."
));
$pdf->Ln(5);

// CABECERA TABLA (MISMO ESTILO INVENTARIO)
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(0,102,204);


$pdf->Cell(10,7,'ID',1,0,'C',true);
$pdf->Cell(45,7,utf8_decode('PERSONAL'),1,0,'C',true);
$pdf->Cell(30,7,utf8_decode('CARGO'),1,0,'C',true);
$pdf->Cell(50,7,utf8_decode('ÁREA'),1,0,'C',true);
$pdf->Cell(20,7,utf8_decode('DESDE'),1,0,'C',true);
$pdf->Cell(20,7,utf8_decode('HASTA'),1,0,'C',true);
$pdf->Cell(15,7,utf8_decode('ESTADO'),1,1,'C',true);

// FILAS
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);

foreach ($filas as $f) {

    $estadoTxt = $f['asignacion_estado'] == 1 ? 'Activo' : 'Anulado';
    $persona = $f['persona_nombre'].' '.$f['persona_apellido'];

    $pdf->Cell(10,7,$f['asignacion_id'],1);
    $pdf->Cell(45,7,utf8_decode($persona),1);
    $pdf->Cell(30,7,utf8_decode($f['cargo_nombre']),1);
    $pdf->Cell(50,7,utf8_decode($f['area_nombre']),1);
    $pdf->Cell(20,7,date('d/m/Y', strtotime($f['asignacion_fecha'])),1);
    $pdf->Cell(
        20,
        7,
        $f['asignacion_fecha_fin']
            ? date('d/m/Y', strtotime($f['asignacion_fecha_fin']))
            : '—',
        1
    );
    $pdf->Cell(15,7,$estadoTxt,1,1);
}

// ---------------- CONCLUSIÓN ----------------
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "     Sin más que agregar, se deja constancia del presente registro general de asignaciones, "
  . "validado por la Dirección de Tecnología y la Delegación de Bienes Públicos para fines administrativos y de control institucional."
));

// ---------------- FIRMAS ----------------
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


// SALIDA
$pdf->Output("I","Reporte_asignacion_general.pdf");
exit;
