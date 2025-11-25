<?php
require_once __DIR__ . "/fpdf/fpdf.php";

class PDF_Asignacion extends FPDF {
    // ID de acta para usar en el footer (se asigna externamente)
    public $acta_id = '';

    function Header() {
        // Membrete grande (ruta)
        $membrete = __DIR__ . '/../img/icons/membrete.png';
        if (file_exists($membrete)) {
            // ancho 190 para cubrir casi todo el ancho (10mm margins por lado)
            $this->Image($membrete, 10, 6, 190);
            $this->Ln(28); // espacio debajo del membrete
        } else {
            // Si no existe la imagen, deja un pequeño espacio
            $this->Ln(15);
        }

        // TÍTULO del acta
        $this->SetFont('Arial','B',12);
        $this->SetTextColor(0,51,102);
        $this->Cell(0,6,utf8_decode('ACTA DE ASIGNACIÓN DE BIENES PATRIMONIALES'), 0, 1, 'C');
        $this->Ln(2);

        // Línea decorativa
        $this->SetDrawColor(0,51,102);
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
?>
