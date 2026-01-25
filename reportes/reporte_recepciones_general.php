<?php
require_once __DIR__ . "/fpdf/fpdf.php";
require_once __DIR__ . "/../bd/conexion.php";

class PDF_Recepcion_General extends FPDF {

    function Header() {

        // MEMBRETE
        $membrete = __DIR__ . '/../img/icons/membrete.png';

        if (file_exists($membrete)) {
            $this->Image($membrete, 10, 5, 190);
            $this->Ln(25);
        } else {
            $this->Ln(10);
        }

        // TÍTULO
        $this->SetFont('Arial','B',12);
        $this->Cell(0,8, utf8_decode('REPORTE GENERAL DE RECEPCIONES'), 0, 1, 'C');
        $this->Ln(2);

        // Línea divisoria
        $this->SetDrawColor(0,0,0);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(100,100,100);
        $this->Cell(0,10,
            utf8_decode("Generado automáticamente · Página ") . $this->PageNo(),
            0,0,'C'
        );
    }

    // --------- helpers para multicelda ----------
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++; $sep = -1; $j = $i; $l = 0; $nl++;
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
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}

$conexion = Conexion::conectar();

// RECEPCIONES
$sql = "
SELECT 
    a.ajuste_id,
    a.ajuste_fecha,
    a.ajuste_descripcion,
    a.ajuste_estado
FROM ajuste a
WHERE a.ajuste_tipo = 1
ORDER BY a.ajuste_id DESC
";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$recepciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PDF
$pdf = new PDF_Recepcion_General('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

$pdf->MultiCell(0,6, utf8_decode(
    "     El presente documento constituye el Reporte General de Recepciones, el cual consolida todas las actas de incorporación de bienes patrimoniales registradas en el sistema. ".
    "En este informe se detallan cada una de las recepciones efectuadas, incluyendo su fecha, descripción, estado administrativo y los bienes asociados, identificados mediante seriales, modelos y categorías correspondientes. ".
    "Este reporte tiene como finalidad garantizar la transparencia en los procesos de ingreso de bienes, así como mantener un registro actualizado que facilite la supervisión, auditoría y control del inventario institucional por parte de la Dirección de Tecnología y la Delegación de Bienes Públicos."
));
$pdf->Ln(5);

foreach ($recepciones as $r) {

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,6, utf8_decode("Recepción N° " . $r['ajuste_id']), 0, 1, 'L');

    $pdf->SetFont('Arial','',10);
    $pdf->Cell(40,6,'Fecha:',0,0);
    $pdf->Cell(0,6,date('d/m/Y', strtotime($r['ajuste_fecha'])),0,1);

    $pdf->Cell(40,6,utf8_decode('Descripción:'),0,0);
    $pdf->MultiCell(0,6, utf8_decode($r['ajuste_descripcion'] ?: '—'));

    $pdf->Cell(40,6,'Estado:',0,0);
    $pdf->Cell(0,6,($r['ajuste_estado']==1?'Activa':'Anulada'),0,1);
    $pdf->Ln(2);

    // CABECERA TABLA (ORDEN CORRECTO)
    $pdf->SetFont('Arial','B',8);
    $pdf->SetFillColor(0,102,204);
    $pdf->Cell(60,7,utf8_decode('ARTÍCULO'),1,0,'C',true);
    $pdf->Cell(30,7,utf8_decode('MODELO'),1,0,'C',true);
    $pdf->Cell(30,7,utf8_decode('MARCA'),1,0,'C',true);
    $pdf->Cell(30,7,utf8_decode('CATEGORÍA'),1,0,'C',true);
    $pdf->Cell(40,7,utf8_decode('SERIAL'),1,1,'C',true);

    $pdf->SetFont('Arial','',9);

    // ITEMS AGRUPADOS
    $sql_items = "
        SELECT 
            art.articulo_nombre,
            art.articulo_modelo,
            COALESCE(m.marca_nombre,'') AS marca_nombre,
            COALESCE(cat.categoria_nombre,'') AS categoria_nombre,
            GROUP_CONCAT(
                IFNULL(s.articulo_serial,'(sin serial)')
                ORDER BY s.articulo_serial_id SEPARATOR ', '
            ) AS seriales
        FROM ajuste_articulo aj
        INNER JOIN articulo_serial s ON aj.articulo_serial_id = s.articulo_serial_id
        INNER JOIN articulo art ON s.articulo_id = art.articulo_id
        LEFT JOIN marca m ON art.marca_id = m.marca_id
        LEFT JOIN clasificacion cl ON art.clasificacion_id = cl.clasificacion_id
        LEFT JOIN categoria cat ON cl.categoria_id = cat.categoria_id
        WHERE aj.ajuste_id = ?
        GROUP BY 
            art.articulo_nombre,
            art.articulo_modelo,
            m.marca_nombre,
            cat.categoria_nombre
        ORDER BY art.articulo_nombre ASC
    ";

    $stmt2 = $conexion->prepare($sql_items);
    $stmt2->execute([$r['ajuste_id']]);
    $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $it) {

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $nb = $pdf->NbLines(40, utf8_decode($it['seriales']));
        $h  = 7 * $nb;

        if ($y + $h > 260) {
            $pdf->AddPage();
            $x = $pdf->GetX();
            $y = $pdf->GetY();
        }

        // ARTÍCULO
        $pdf->Rect($x, $y, 60, $h);
        $pdf->MultiCell(60, $h, utf8_decode($it['articulo_nombre']), 0, 'L');
        $pdf->SetXY($x + 60, $y);

        // MODELO
        $pdf->Rect($x + 60, $y, 30, $h);
        $pdf->MultiCell(30, $h, utf8_decode($it['articulo_modelo'] ?: '—'), 0, 'C');
        $pdf->SetXY($x + 90, $y);

        // MARCA
        $pdf->Rect($x + 90, $y, 30, $h);
        $pdf->MultiCell(30, $h, utf8_decode($it['marca_nombre']), 0, 'C');
        $pdf->SetXY($x + 120, $y);

        // CATEGORÍA
        $pdf->Rect($x + 120, $y, 30, $h);
        $pdf->MultiCell(30, $h, utf8_decode($it['categoria_nombre']), 0, 'C');
        $pdf->SetXY($x + 150, $y);

        // SERIAL (DINÁMICO)
        $pdf->Rect($x + 150, $y, 40, $h);
        $pdf->MultiCell(40, 7, utf8_decode($it['seriales']), 0, 'L');

        $pdf->Ln(0);
    }

    $pdf->Ln(22);
}

// CONCLUSIÓN
$pdf->MultiCell(0,6, utf8_decode(
    "     En virtud de la información expuesta, se deja constancia de que todas las recepciones aquí documentadas han sido procesadas según los lineamientos establecidos por los órganos competentes. ".
    "Con lo anterior, se certifica la validez del contenido del presente informe, siendo elevado para los fines administrativos y de verificación que correspondan."
));
$pdf->Ln(10);

// FIRMAS
$pdf->Ln(20);
$pdf->SetFont('Arial','',9);

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

$pdf->Output("I", "Reporte_Recepciones_General.pdf");
exit;
