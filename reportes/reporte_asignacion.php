<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/../bd/conexion.php";

//CLASE PDF UNIFICADA
class PDF_Asignacion extends FPDF {

    public $acta_id = '';

    function Header() {
        // Membrete
        $membrete = __DIR__ . '/../img/icons/membrete.png';
        if (file_exists($membrete)) {
            $this->Image($membrete, 10, 6, 190);
            $this->Ln(28);
        } else {
            $this->Ln(15);
        }

        // Título
        $this->SetFont('Arial','B',12);
        $this->Cell(0,6,utf8_decode('ACTA DE ASIGNACIÓN DE BIENES PATRIMONIALES AL USUARIO'),0,1,'C');
        $this->Ln(2);

        // Línea
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(100,100,100);

        // Acta dinámica
        $acta = $this->acta_id ? "Acta N°: " . $this->acta_id . " · " : "";
        $this->Cell(0,6,utf8_decode($acta . 'Generado automáticamente · Página ') . $this->PageNo(), 0, 0, 'C');
    }

    // FILAS MULTILÍNEA (RowMulti) 

    function RowMulti($row, $widths, $aligns = []) {
        $nb = 0;

        // Calcular número máximo de líneas
        for ($i = 0; $i < count($row); $i++) {
            $nb = max($nb, $this->NbLines($widths[$i], utf8_decode($row[$i])));
        }

        $h = 5 * $nb;

        // Salto de página automático
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage();
        }

        // Celdas
        for ($i = 0; $i < count($row); $i++) {
            $w = $widths[$i];
            $a = isset($aligns[$i]) ? $aligns[$i] : 'L';

            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 5, utf8_decode($row[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    //FUNCIÓN QUE FALTABA (NbLines) — FIX 

    function NbLines($w, $txt) {
        $txt = utf8_decode($txt);
        $cw = &$this->CurrentFont['cw'];

        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }

        $wmax = ($w - 2*$this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);

        if ($nb > 0 && $s[$nb-1] == "\n") {
            $nb--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = $s[$i];

            if ($c == "\n") {
                $i++; $sep = -1; $j = $i; $l = 0; $nl++;
                continue;
            }

            if ($c == ' ') $sep = $i;

            $l += $cw[$c] ?? 500; // fallback si falta el ancho

            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}

//CONSULTAS DE DATOS
$conexion = Conexion::conectar();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}
$id = intval($_GET['id']);

// Datos principales
$sql = "
SELECT a.asignacion_id, a.asignacion_fecha, a.asignacion_fecha_fin,
       p.persona_nombre, p.persona_apellido, p.persona_cedula,
       ar.area_nombre
FROM asignacion a
INNER JOIN persona p ON p.persona_id = a.persona_id
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

// Bienes asignados

$sql_bienes = "
SELECT 
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

//SEPARACIÓN DE BIENES

$mobiliario = [];
$equipos = [];

foreach ($bienes as $b) {
    $codigo = ($b['clasificacion_codigo'] ? $b['clasificacion_codigo'] . '-' : '') . $b['articulo_codigo'];

    if (intval($b['categoria_tipo']) == 0) {
        $mobiliario[] = [
            'codigo' => $codigo,
            'clasificacion' => $b['clasificacion_nombre'],
            'descripcion' => $b['articulo_descripcion'] ?: $b['articulo_nombre']
        ];
    } else {
        $equipos[] = [
            'codigo' => $codigo,
            'articulo' => $b['articulo_nombre'],
            'clasificacion' => $b['clasificacion_nombre'],
            'marca' => $b['marca_nombre'],
            'modelo' => $b['articulo_modelo'],
            'serial' => $b['serial']
        ];
    }
}

//PDF      
$pdf = new PDF_Asignacion('P','mm','A4');
$pdf->acta_id = $asig['asignacion_id'];
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true,20);
$pdf->SetFont('Arial','',11);

// Código superior
$pdf->Cell(0,6,utf8_decode('UNES/DTIT-DBN/') . date('Y') . '/' . str_pad($id,3,'0',STR_PAD_LEFT),0,1,'R');
$pdf->Ln(4);

// Desarrollo
$texto = "     Yo, ANIREXIS GÓMEZ, titular de la cédula N° 19.155.677, en mi condición de cargo como DIRECTORA NACIONAL DE TECNOLOGÍA, hago entrega de los recursos que indican en presente documento para el uso exclusivo de las funciones asignadas, quedando así constancia de la asignación en buena condición y conservación; por lo tanto, cuya características de los bienes, se describen a continuación: ";
$pdf->MultiCell(0,6,utf8_decode($texto));
$pdf->Ln(4);

// DATOS
$pdf->SetFont('Arial','',10);
$pdf->Cell(50,6,utf8_decode('Área:'),0,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,6,utf8_decode($asig['area_nombre']),0,1,'L');

$pdf->SetFont('Arial','',10);
$pdf->Cell(50,6,utf8_decode('Fecha de Asignación:'),0,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,6,date('d/m/Y', strtotime($asig['asignacion_fecha'])),0,1,'L');

if (!empty($asig['asignacion_fecha_fin'])) {
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(50,6,'Fecha Fin:',0,0,'L');
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,6,date('d/m/Y', strtotime($asig['asignacion_fecha_fin'])),0,1,'L');
}

$pdf->Ln(10);

// TABLA MOBILIARIO

if (count($mobiliario) > 0) {
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,7,'MOBILIARIO',0,1,'C');
    $pdf->Ln(3);


    $pdf->SetFont('Arial','B',8);
    $pdf->SetFillColor(0,120,255);
    $pdf->Cell(30,7, utf8_decode('CÓDIGO'),1,0,'C',true);
    $pdf->Cell(60,7,utf8_decode('CLASIFICACIÓN'),1,0,'C',true);
    $pdf->Cell(100,7,utf8_decode('DESCRIPCIÓN'),1,1,'C',true);

    $pdf->SetFont('Arial','',9);
    foreach ($mobiliario as $m) {
        $pdf->RowMulti([$m['codigo'], $m['clasificacion'], $m['descripcion']], [30,60,100], ['C','L','L']);
    }
    $pdf->Ln(10);
}

//TABLA EQUIPOS

if (count($equipos) > 0) {
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,7,utf8_decode('EQUIPO TECNOLÓGICO'),0,1,'C');
    $pdf->Ln(3);


    // NUEVA TABLA con ARTÍCULO incluido:
    // Código (25) | Articulo (35) | Clasificación (35) | Marca (30) | Modelo (35) | Serial (30) = 190 mm
    $pdf->SetFont('Arial','B',8);
    $pdf->SetFillColor(0,120,255);
    $pdf->Cell(30,7,utf8_decode('CÓDIGO'),1,0,'C',true);
    $pdf->Cell(35,7,utf8_decode('ARTÍCULO'),1,0,'C',true);      
    $pdf->Cell(35,7,utf8_decode('CLASIFICACIÓN'),1,0,'C',true);
    $pdf->Cell(30,7,utf8_decode('MARCA'),1,0,'C',true);
    $pdf->Cell(35,7,utf8_decode('MODELO'),1,0,'C',true);
    $pdf->Cell(25,7,utf8_decode('SERIAL'),1,1,'C',true);

    $pdf->SetFont('Arial','',9);
    foreach ($equipos as $e) {
        $pdf->RowMulti(
            [$e['codigo'], $e['articulo'], $e['clasificacion'], $e['marca'], $e['modelo'], $e['serial']],
            [30,35,35,30,35,25],
            ['C','L','L','L','L','L']
        );
    }

    $pdf->Ln(6);
}

//CONTROL DE ESPACIO PARA CONCLUSIÓN + FIRMAS
// Espacio mínimo requerido para concluir bien el reporte
$espacioNecesario = 50; // mm → suficiente para conclusión + firmas

// Si no queda espacio suficiente en la página actual:
if ($pdf->GetY() + $espacioNecesario > 260) {  
    $pdf->AddPage();
}

// CONCLUSION
$pdf->Ln(0);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6,utf8_decode("       Se deja constancia que el ciudadano(a) {$asig['persona_nombre']} {$asig['persona_apellido']} portador de la Cédula de Identidad N° {$asig['persona_cedula']} recibió los bienes antes especificados en buenas condiciones de uso y conservación, comprometiéndose a cumplir con las normas y responsabilidades propias del cargo."), 0, 'J');

// FIRMA
$pdf->Ln(30);
$pdf->Cell(80,6,'______________________________',0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,'______________________________',0,1,'C');

$pdf->SetFont('Arial','B',9);
$pdf->Cell(80,6,utf8_decode('Ing. ANIREXIS GÓMEZ'),0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,utf8_decode('Lcda. DAYANIS ARELLANO'),0,1,'C');

$pdf->SetFont('Arial','',9);
$pdf->Cell(80,6,utf8_decode('Directora Nacional de Tecnología'),0,0,'C');
$pdf->Cell(30,6,'',0,0);
$pdf->Cell(80,6,utf8_decode('Delegada de Bienes Públicos'),0,1,'C');

$pdf->Ln(12);
$pdf->Cell(0,6,'______________________________',0,1,'C');
$pdf->SetFont('Arial','B',9);
$pdf->Cell(0,5,utf8_decode($asig['persona_nombre'] . ' ' . $asig['persona_apellido']),0,1,'C');
$pdf->Cell(0,5,'CI: ' . $asig['persona_cedula'],0,1,'C');

$pdf->Output('I', "Acta_Asignacion_{$id}.pdf");
exit;


