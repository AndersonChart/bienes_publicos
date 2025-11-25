<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/plantilla_asignacion.php";
require_once __DIR__ . "/../bd/conexion.php";

$conexion = Conexion::conectar();

// obtener id por GET (validar)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Falta el parámetro id de la asignación.");
}
$id = intval($_GET['id']);

// 1) DATOS PRINCIPALES (asignacion + persona + cargo + area)
$sql = "
SELECT a.asignacion_id, a.asignacion_fecha, a.asignacion_fecha_fin,
       p.persona_id, p.persona_nombre, p.persona_apellido, p.persona_cedula, p.persona_correo,
       c.cargo_nombre,
       ar.area_nombre
FROM asignacion a
INNER JOIN persona p ON p.persona_id = a.persona_id
INNER JOIN cargo c ON c.cargo_id = p.cargo_id
INNER JOIN area ar ON ar.area_id = a.area_id
WHERE a.asignacion_id = ?
LIMIT 1
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
$asig = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asig) {
    die("Asignación no encontrada.");
}

// 2) BIENES ASIGNADOS (unir asignacion_articulo -> articulo_serial -> articulo -> clasificacion -> categoria -> marca)
$sql_bienes = "
SELECT 
    aa.asignacion_articulo_id,
    s.articulo_serial AS serial,
    a.articulo_codigo,
    a.articulo_nombre,
    a.articulo_modelo,
    a.articulo_descripcion,
    cl.clasificacion_nombre,
    cl.clasificacion_codigo,
    cat.categoria_tipo,
    m.marca_nombre
FROM asignacion_articulo aa
INNER JOIN articulo_serial s ON s.articulo_serial_id = aa.articulo_serial_id
INNER JOIN articulo a ON a.articulo_id = s.articulo_id
INNER JOIN clasificacion cl ON cl.clasificacion_id = a.clasificacion_id
INNER JOIN categoria cat ON cat.categoria_id = cl.categoria_id
LEFT JOIN marca m ON m.marca_id = a.marca_id
WHERE aa.asignacion_id = ?
ORDER BY a.articulo_nombre ASC
";
$stmt2 = $conexion->prepare($sql_bienes);
$stmt2->execute([$id]);
$bienes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// separar en dos grupos: mobiliario (categoria_tipo = 0) y equipos (categoria_tipo = 1)
$mobiliario = [];
$equipos = [];
foreach ($bienes as $b) {
    if (isset($b['categoria_tipo']) && intval($b['categoria_tipo']) == 0) {
        // mobiliario: código, clasificación, descripción
        $mobiliario[] = [
            'codigo' => ($b['clasificacion_codigo'] ? $b['clasificacion_codigo'] . '-' : '') . $b['articulo_codigo'],
            'clasificacion' => $b['clasificacion_nombre'],
            'descripcion' => $b['articulo_descripcion'] ? $b['articulo_descripcion'] : $b['articulo_nombre']
        ];
    } else {
        // equipos: código, clasificación, marca, modelo, serial
        $equipos[] = [
            'codigo' => ($b['clasificacion_codigo'] ? $b['clasificacion_codigo'] . '-' : '') . $b['articulo_codigo'],
            'clasificacion' => $b['clasificacion_nombre'],
            'marca' => $b['marca_nombre'],
            'modelo' => $b['articulo_modelo'],
            'serial' => $b['serial']
        ];
    }
}

// 3) Crear PDF
$pdf = new PDF_Asignacion('P','mm','A4');
$pdf->acta_id = $asig['asignacion_id']; // pie dinámico
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);
$pdf->SetFont('Arial','',11);

// Fecha y destinatario (alineado a la derecha)
$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell(0,6,utf8_decode('UNES/DITT-DBN/'). date('Y') . '/' . str_pad($asig['asignacion_id'],3,'0',STR_PAD_LEFT), 0,1,'R');
$pdf->Ln(4);

// Texto introductorio (ajústalo a tu redacción institucional)
$texto = "Yo, {$asig['persona_nombre']} {$asig['persona_apellido']}, titular de la cédula N° {$asig['persona_cedula']}, en mi condición de {$asig['cargo_nombre']}, hago entrega para el uso exclusivo del ciudadano designado en el presente Acta los bienes relacionados en las tablas a continuación, para el cumplimiento de las funciones asignadas al ciudadano, quedando así constancia de la asignación en buena condición y conservacion.";
$pdf->MultiCell(0,6,utf8_decode($texto));
$pdf->Ln(4);

// Datos de área y periodo
$pdf->SetFont('Arial','',10);
$pdf->Cell(50,6,'Área:',0,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,6,utf8_decode($asig['area_nombre']),0,1,'L');

$pdf->SetFont('Arial','',10);
$pdf->Cell(50,6,'Fecha de asignación:',0,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,6,date('d/m/Y', strtotime($asig['asignacion_fecha'])),0,1,'L');

if (!empty($asig['asignacion_fecha_fin'])) {
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(50,6,'Fecha fin:',0,0,'L');
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,6,date('d/m/Y', strtotime($asig['asignacion_fecha_fin'])),0,1,'L');
}

$pdf->Ln(6);

// ----- Tabla: MOBILIARIO -----
if (count($mobiliario) > 0) {
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(230,230,230);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(0,7,utf8_decode('MOBILIARIO'),1,1,'L',true);
    $pdf->Ln(2);

    // encabezado columnas: Código (25), Clasificación (40), Descripción (125) -> total 190
    $pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(200,200,200);
    $pdf->Cell(25,7,'CÓDIGO',1,0,'C',true);
    $pdf->Cell(40,7,'CLASIFICACIÓN',1,0,'C',true);
    $pdf->Cell(125,7,'DESCRIPCIÓN',1,1,'C',true);

    $pdf->SetFont('Arial','',9);
    foreach ($mobiliario as $m) {
        $pdf->RowMulti([$m['codigo'], $m['clasificacion'], $m['descripcion']], [25,40,125], ['C','L','L']);
    }
    $pdf->Ln(6);
}

// ----- Tabla: EQUIPOS -----
if (count($equipos) > 0) {
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(230,230,230);
    $pdf->Cell(0,7,utf8_decode('EQUIPOS'),1,1,'L',true);
    $pdf->Ln(2);

    // encabezado: Código (25) Clasificación (40) Marca (35) Modelo (45) Serial (45) -> total 190
    $pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(200,200,200);
    $pdf->Cell(25,7,'CÓDIGO',1,0,'C',true);
    $pdf->Cell(40,7,'CLASIFICACIÓN',1,0,'C',true);
    $pdf->Cell(35,7,'MARCA',1,0,'C',true);
    $pdf->Cell(45,7,'MODELO',1,0,'C',true);
    $pdf->Cell(45,7,'SERIAL',1,1,'C',true);

    $pdf->SetFont('Arial','',9);
    foreach ($equipos as $e) {
        $pdf->RowMulti([$e['codigo'], $e['clasificacion'], $e['marca'], $e['modelo'], $e['serial']], [25,40,35,45,45], ['C','L','L','L','L']);
    }
    $pdf->Ln(6);
}

// PIE DE PÁGINA: firma / constancia
$pdf->Ln(8);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6,utf8_decode("Se deja constancia que el ciudadano {$asig['persona_nombre']} {$asig['persona_apellido']} recibió los bienes antes especificados en buenas condiciones de uso y conservación, comprometiéndose a cumplir con las normas y responsabilidades propias del cargo."), 0, 'J');

$pdf->Ln(12);
// línea para firma
$pdf->Cell(0,6,'______________________________',0,1,'C');
$pdf->Cell(0,5,utf8_decode($asig['persona_nombre'] . ' ' . $asig['persona_apellido']),0,1,'C');
$pdf->Cell(0,5,'CI: ' . $asig['persona_cedula'],0,1,'C');

$pdf->Output('I', "Acta_Asignacion_{$id}.pdf");
exit;
