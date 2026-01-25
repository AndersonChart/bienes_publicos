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

        $this->SetFont('Arial','B',12);
        $this->Cell(0,6,utf8_decode('ACTA DE RECEPCION DE BIENES PATRIMONIALES'), 0, 1, 'C');
        $this->Ln(2);

        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(100,100,100);
        $acta = $this->acta_id ? "Acta N°: " . $this->acta_id . " · " : "";
        $this->Cell(0,6,utf8_decode($acta . 'Generado automáticamente · Página ') . $this->PageNo(), 0, 0, 'C');
    }

    // ===== helper original =====
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) $i++;
                else $i = $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        }
        return $nl;
    }
}

/* ===============================
   CONEXIÓN
================================ */
$conexion = Conexion::conectar();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de recepción inválido");
}
$id = intval($_GET['id']);

/* ===============================
   DATOS DE RECEPCIÓN
================================ */
$stmt = $conexion->prepare("
    SELECT ajuste_id, ajuste_fecha, ajuste_descripcion
    FROM ajuste
    WHERE ajuste_id = ? AND ajuste_tipo = 1
    LIMIT 1
");
$stmt->execute([$id]);
$recepcion = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===============================
   ARTÍCULOS AGRUPADOS POR CÓDIGO
================================ */
$stmt2 = $conexion->prepare("
    SELECT 
        a.articulo_codigo,
        a.articulo_nombre,
        GROUP_CONCAT(
            IFNULL(s.articulo_serial,'(sin serial)')
            ORDER BY s.articulo_serial_id SEPARATOR ', '
        ) AS seriales
    FROM ajuste_articulo aa
    INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
    INNER JOIN articulo a ON s.articulo_id = a.articulo_id
    WHERE aa.ajuste_id = ?
    GROUP BY a.articulo_codigo, a.articulo_nombre
    ORDER BY a.articulo_nombre ASC
");
$stmt2->execute([$id]);
$articulos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   PDF
================================ */
$pdf = new PDF_Recepcion();
$pdf->acta_id = $recepcion["ajuste_id"]; 
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell(0,6,utf8_decode('UNES/DTIT-DBN/'). date('Y') . '/' . str_pad($recepcion['ajuste_id'],3,'0',STR_PAD_LEFT), 0,1,'R');
$pdf->Ln(4);

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

$pdf->Ln(4);
$pdf->MultiCell(0,6, utf8_decode(
    "     El presente documento corresponde al Acta de Recepción de Bienes Patrimoniales, mediante la cual se deja constancia del ingreso de los artículos detallados a continuación. " .
    "Esta recepción constituye un registro oficial destinado a garantizar la debida incorporación, control, trazabilidad y resguardo de los bienes que pasan a formar parte del inventario institucional. " .
    "La información aquí reflejada ha sido verificada conforme a los procedimientos establecidos por la normativa interna."
));
$pdf->Ln(6);


/* TABLA */
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(0,102,204);
$pdf->Cell(50,7,utf8_decode("CÓDIGO"),1,0,'C',true);
$pdf->Cell(65,7,utf8_decode("NOMBRE"),1,0,'C',true);
$pdf->Cell(70,7,utf8_decode("SERIALES"),1,1,'C',true);

$pdf->SetFont('Arial','',9);

/* ===== FILAS (SOLO SERIALES DINÁMICOS) ===== */
foreach ($articulos as $a) {

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // calcular altura según seriales
    $nb = $pdf->NbLines(70, utf8_decode($a['seriales']));
    $h  = 7 * $nb;

    if ($y + $h > 260) {
        $pdf->AddPage();
        $x = $pdf->GetX();
        $y = $pdf->GetY();
    }

    // CÓDIGO
    $pdf->Rect($x, $y, 50, $h);
    $pdf->MultiCell(50, $h, $a['articulo_codigo'], 0, 'C');
    $pdf->SetXY($x + 50, $y);

    // NOMBRE
    $pdf->Rect($x + 50, $y, 65, $h);
    $pdf->MultiCell(65, $h, utf8_decode($a['articulo_nombre']), 0, 'L');
    $pdf->SetXY($x + 115, $y);

    // SERIALES (única columna ajustable)
    $pdf->Rect($x + 115, $y, 70, $h);
    $pdf->MultiCell(70, 7, utf8_decode($a['seriales']), 0, 'L');

    $pdf->Ln(0);
}

/* ===============================
   5) CONCLUSIÓN Y FIRMAS
================================ */
if ($pdf->GetY() + 50 > 260) {
    $pdf->AddPage();
}

$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6, utf8_decode(
    "     Sin más que agregar, se deja constancia del presente documento institucional, validado para el debido control patrimonial."
));

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

$pdf->Output("I","Reporte_Recepcion_".$id.".pdf");
