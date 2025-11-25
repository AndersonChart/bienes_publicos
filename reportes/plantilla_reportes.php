<?php
require_once __DIR__ . "/fpdf/fpdf.php";


class PDF_MC_Table extends FPDF {

    function Header() {

        // MEMBRETE
        $this->Image('../img/icons/membrete.png', 10, 8, 190);

        $this->Image('../img/icons/logouni.png', 10, 30, 20);
        

        // TÍTULO PRINCIPAL
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 50, utf8_decode('SISTEMA DE CONTROL'), 0, 1, 'C');

        // SUBTÍTULO
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, utf8_decode('REPORTE GENERAL'), 0, 1, 'C');

        // LÍNEA
        $this->Ln(3);
        $this->SetDrawColor(0, 0, 0);
        $this->Line(10, 28, 200, 28);

        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Generado automáticamente · Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

