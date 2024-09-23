<?php

session_start();

require '../fpdf/fpdf.php';

$clientes = isset($_SESSION["CLIENTS_EXPORTS"])
    ? $_SESSION["CLIENTS_EXPORTS"]
    : false;


// Creación del objeto de la clase heredada
$pdf = new FPDF();
$pdf->AliasNbPages();
// $pdf->SetMargins(20, 20, 20);
$pdf->AddFont('Manrope-Regular', 'I', 'Manrope-Regular.php');
$pdf->AddFont('Lilex-Medium', 'I', 'Lilex-Medium.php');
$pdf->AddFont('Lilex-Bold', 'I', 'Lilex-Bold.php');
$pdf->AddFont('Lilex', 'I', 'Lilex.php');
$pdf->AddFont('Roboto-Bold', 'I', 'Roboto-Bold.php');

$pdf->AddPage('L', 'A4');
$pdf->SetFont('Lilex-Bold', 'I', 10);
$pdf->Cell(0, 8, 'CLIENTES ISIS INTERNET ' . "Clientes", 'T L R B', 1, 'C', 0);

$pdf->SetFont('Lilex-Bold', 'I', 8);
$pdf->Cell(70, 8, 'Nombres', 'L B', 0, 'C', 0);
$pdf->Cell(50, 8, 'Colonia', 'L B', 0, 'C', 0);
$pdf->Cell(27, 8, 'Telefono', 'L B', 0, 'C', 0);
// $pdf->Cell(65,8,'Email','L B', 0, 'C', 0);
$pdf->Cell(35, 8, 'IPv4', 'L B', 0, 'C', 0);
$pdf->Cell(40, 8, 'MAC', 'L B', 0, 'C', 0);
$pdf->Cell(15, 8, 'Corte', 'L B', 0, 'C', 0);
$pdf->Cell(0, 8, 'Pago', 'L B R', 1, 'C', 0);
$pdf->Ln(1);
$count = 0;
foreach ($clientes as $cliente) {
    $count++;
    $pdf->setTextColor(0, 0, 0);
    $pdf->SetFont('Lilex-Bold', 'I', 7);

    $color = ($count % 2) == 0 ? $pdf->setFillColor(203, 213, 225) : $pdf->setFillColor(255, 255, 255);
    if (($count % 16) == 0) {
        $pdf->SetFont('Lilex-Bold', 'I', 8);
        $pdf->Cell(70, 8, 'Nombres', 'L B T', 0, 'C', 0);
        $pdf->Cell(50, 8, 'Colonia', 'L B T', 0, 'C', 0);
        $pdf->Cell(27, 8, 'Telefono', 'L B T', 0, 'C', 0);
        // $pdf->Cell(65,8,'Email','L B T', 0, 'C', 0);
        $pdf->Cell(35, 8, 'IPv4', 'L B T', 0, 'C', 0);
        $pdf->Cell(40, 8, 'MAC', 'L B T', 0, 'C', 0);
        $pdf->Cell(15, 8, 'Corte', 'L B T', 0, 'C', 0);
        $pdf->Cell(0, 8, 'Pago', 'L B T R', 1, 'C', 0);
        $pdf->Ln(1);
    }

    $pdf->Cell(70, 8, strtoupper($cliente['nombres']), 0, 0, 'C', 1);
    $pdf->Cell(50, 8, $pdf->eliminar_acentos($cliente['nombre_colonia']), 0, 0, 'C', 1);
    $pdf->Cell(27, 8, $cliente['cliente_telefono'], 0, 0, 'C', 1);
    $pdf->Cell(35, 8, $cliente['cliente_ip'], 0, 0, 'C', 1);
    $pdf->Cell(40, 8, $cliente['cliente_mac'], 0, 0, 'C', 1);
    $pdf->Cell(15, 8, $cliente['cliente_corte'], 0, 0, 'C', 1);
    $pdf->Cell(0, 8, $cliente['mensualidad'], 0, 1, 'C', 1);
    $pdf->Ln(1);
}

$pdf->Output();