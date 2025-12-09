<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/../bd/conexion.php";

class PDF_Recepcion extends FPDF {
    public $acta_id = '';

    function Header() {
        $membrete = __DIR__ . '/../img/icons/membrete.png';
        if (file_exists($membrete)) {
            $this->Image($membrete, 10, 6, 190);
            $this->Ln(28); 
        } else {
            $this->Ln(15);
        }

        // TÍTULO DEL ACTA
        $this->SetFont('Arial','B',12);
        $this->SetTextColor(0,0,0);
        $this->Cell(0,6,utf8_decode('ACTA DE RECEPCION DE BIENES PATRIMONIALES'), 0, 1, 'C');
        $this->Ln(2);

        // LINEA DECORATIVA
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(100,100,100);

        // Acta dinámica (ej: Acta N°: 5) y número de página
        $acta = $this->acta_id ? "Acta N°: " . $this->acta_id . " · " : "";
        $this->Cell(0,6,utf8_decode($acta . 'Generado automáticamente · Página ') . $this->PageNo(), 0, 0, 'C');
    }

    /**
     * Helper para dibujar una fila con multicelda para la descripción.
     * Recibe un array $row y un array $widths (en mm).
     */
    function RowMulti($row, $widths, $aligns = []) {
        $nb = 0;
        // calcular el número máximo de líneas que ocupará cada celda
        for ($i = 0; $i < count($row); $i++) {
            $s = utf8_decode($row[$i]);
            $w = $widths[$i];
            $nbcol = $this->NbLines($w, $s);
            if ($nbcol > $nb) $nb = $nbcol;
        }
        $h = 5 * $nb; // altura de la fila = 5 mm por línea

        // Salto de página si es necesario
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage();
        }

        // Dibujar celdas
        for ($i = 0; $i < count($row); $i++) {
            $w = $widths[$i];
            $a = isset($aligns[$i]) ? $aligns[$i] : 'L';
            // guardar posición
            $x = $this->GetX();
            $y = $this->GetY();
            // cuadro
            $this->Rect($x, $y, $w, $h);
            // texto
            $this->MultiCell($w, 5, utf8_decode($row[$i]), 0, $a);
            // volver al tope de la fila
            $this->SetXY($x + $w, $y);
        }
        // mover a la siguiente línea
        $this->Ln($h);
    }

    // Calcula cuántas líneas ocupará un texto en una celda de ancho w
    function NbLines($w, $txt) {
        // similar a la implementación de FPDF
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb-1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
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

$conexion = Conexion::conectar();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de recepción inválido");
}
$id = intval($_GET['id']);

// 1) Información principal de la recepción
$sql = "
SELECT ajuste_id, ajuste_fecha, ajuste_descripcion
FROM ajuste
WHERE ajuste_id = ? AND ajuste_tipo = 1
LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
$recepcion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recepcion) {
    die("Recepción no encontrada");
}

// 2) Artículos asociados — UNA FILA POR SERIAL
$sql2 = "
SELECT 
    a.articulo_codigo,
    a.articulo_nombre,
    s.articulo_serial AS serial
FROM ajuste_articulo aa
INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
INNER JOIN articulo a ON s.articulo_id = a.articulo_id
WHERE aa.ajuste_id = ?
ORDER BY a.articulo_nombre ASC, s.articulo_serial_id ASC
";

$stmt2 = $conexion->prepare($sql2);
$stmt2->execute([$id]);
$articulos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// PDF
$pdf = new PDF_Recepcion();
$pdf->acta_id = $recepcion["ajuste_id"]; 
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// CODIGO SUPERIOR
$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell(0,6,utf8_decode('UNES/DTIT-DBN/'). date('Y') . '/' . str_pad($recepcion['ajuste_id'],3,'0',STR_PAD_LEFT), 0,1,'R');
$pdf->Ln(4);

// DATOS DE LA CABECERA
$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,7,"ID Recepcion:",0,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,7,$recepcion["ajuste_id"],0,1,'L');

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,7,"Fecha:",0,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,7,date("d/m/Y", strtotime($recepcion["ajuste_fecha"])),0,1,'L');

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,7,utf8_decode("Descripción:"),0,0,'L');
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,7,utf8_decode($recepcion["ajuste_descripcion"]));

// ----- INTRODUCCIÓN -----
$pdf->Ln(4);
$pdf->MultiCell(0,6, utf8_decode(
    "     El presente documento corresponde al Acta de Recepción de Bienes Patrimoniales, mediante la cual se deja constancia del ingreso de los artículos detallados a continuación. " .
    "Esta recepción constituye un registro oficial destinado a garantizar la debida incorporación, control, trazabilidad y resguardo de los bienes que pasan a formar parte del inventario institucional. " .
    "La información aquí reflejada ha sido verificada conforme a los procedimientos establecidos por la normativa interna, asegurando la correcta identificación de los bienes recibidos, así como la coincidencia entre su descripción, códigos, modelos y seriales asociados. " .
    "Se deja constancia de que los artículos incorporados cumplen con el proceso administrativo correspondiente y pasan a estar bajo la supervisión de la Dirección de Tecnología y la Delegación de Bienes Públicos."
));
$pdf->Ln(6);

// ----- TABLA -----
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(0,102,204);


$pdf->Cell(50,7,utf8_decode("CÓDIGO"),1,0,'C',true);
$pdf->Cell(65,7,utf8_decode("NOMBRE"),1,0,'C',true);
$pdf->Cell(70,7,utf8_decode("SERIAL"),1,1,'C',true);

// Reset color texto normal
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);

// FILAS – UNA POR SERIAL
foreach ($articulos as $a) {

    $serial = $a["serial"] ?: "(sin serial)";

    $pdf->Cell(50,7,$a["articulo_codigo"],1,0,'C');
    $pdf->Cell(65,7,utf8_decode($a["articulo_nombre"]),1,0,'L');
    $pdf->Cell(70,7,utf8_decode($serial),1,1,'L');
}

//CONTROL DE ESPACIO PARA CONCLUSIÓN + FIRMAS
// Espacio mínimo requerido para concluir bien el reporte
$espacioNecesario = 50; // mm → suficiente para conclusión + firmas

// Si no queda espacio suficiente en la página actual:
if ($pdf->GetY() + $espacioNecesario > 260) {  
    $pdf->AddPage();
}

// ----- CONCLUSIÓN -----
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "     Sin más que agregar, se deja constancia del presente documento institucional, validado por la Dirección y la Delegación correspondiente para el debido registro y control patrimonial."
));

// ----- FIRMAS -----
$pdf->Ln(30);

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
$pdf->Output("I","Reporte_Recepcion_".$id.".pdf");

?>
