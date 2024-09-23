<?php

$data = file_get_contents("../../sacc/api/controller/database/clientes/clientes.json");
$clientes = json_decode($data, true);
require('../../sacc/assets/fpdf/fpdf.php');
include_once('../../dbconfig/connection.php');

class PDF extends FPDF {
  // Cabecera de página
  function Header()  {
      // Imagen de encabezado
      $this->Image('../../sacc/assets/images/isisinternet.png',10,-3,30);
      // Logo
      $this->Image('../../sacc/assets/images/fondo-header.png',207,5,80, 15);
      // Arial bold 15
      $this->SetFont('Arial','B',12);
      // Color del texto //
      $this->SetTextColor(37, 99, 235);
      // Movernos a la derecha
      $this->Cell(80);
      // Fuente
      $this->AddFont('Lilex-Bold','I', 'Lilex-Bold.php');
      // Añadir la fuente
      $this->SetFont('Lilex-Bold', 'I', 12);
      // Título
      $this->Cell(100,6,utf8_decode('INTERNET SOFTWARE AND INFORMATIC SERVICES'),0,0,'R');
      // Salto de línea
      $this->Ln(20);
  }

  // Pie de página
  function Footer()
  {
      // Posición: a 1,5 cm del final
      $this->SetY(-15);
      // Arial italic 8
      $this->SetFont('Arial','I',8);
      // Número de página
      $this->MultiCell(0,4, utf8_decode('Avenida Lázaro Cárdenas, Colonia Centro, Cárdenas Tabasco, frente a Bodega Aurrera planta alta, local n° 1'));
  }
}

// Creación del objeto de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
// $pdf->SetMargins(20, 20, 20);
$pdf->AddFont('Manrope-Regular','I', 'Manrope-Regular.php');
$pdf->AddFont('Lilex-Medium','I', 'Lilex-Medium.php');
$pdf->AddFont('Lilex-Bold','I', 'Lilex-Bold.php');
$pdf->AddFont('Lilex','I', 'Lilex.php');
$pdf->AddFont('Roboto-Bold','I', 'Roboto-Bold.php');

$pdf->AddPage('L', 'A4');
$pdf->SetFont('Lilex-Bold', 'I', 10);
$pdf->Cell(0, 8, 'CLIENTES ISIS INTERNET ' . $_GET['title'], 'T L R B', 1, 'C', 0);

$pdf->SetFont('Lilex-Bold','I', 8);
$pdf->Cell(70,8,'Nombres', 'L B', 0, 'C', 0);
$pdf->Cell(50,8,'Colonia', 'L B', 0, 'C', 0);
$pdf->Cell(27,8,'Telefono','L B', 0, 'C', 0);
// $pdf->Cell(65,8,'Email','L B', 0, 'C', 0);
$pdf->Cell(35,8,'IPv4','L B', 0, 'C', 0);
$pdf->Cell(40,8,'MAC','L B', 0, 'C', 0);
$pdf->Cell(15,8,'Corte','L B', 0, 'C', 0);
$pdf->Cell(0, 8, 'Pago', 'L B R', 1, 'C', 0);
$pdf->Ln(1);
$count = 0;
foreach ($clientes as $cliente) {
  $count++;
  $pdf->setTextColor(0,0,0);
  $pdf->SetFont('Lilex-Bold','I',7);

  $color = ($count % 2) == 0 ? $pdf->setFillColor(203, 213, 225) : $pdf->setFillColor(255, 255, 255);
  if (($count % 16) == 0) {
    $pdf->SetFont('Lilex-Bold','I', 8);
    $pdf->Cell(70,8,'Nombres', 'L B T', 0, 'C', 0);
    $pdf->Cell(50,8,'Colonia', 'L B T', 0, 'C', 0);
    $pdf->Cell(27,8,'Telefono','L B T', 0, 'C', 0);
    // $pdf->Cell(65,8,'Email','L B T', 0, 'C', 0);
    $pdf->Cell(35,8,'IPv4','L B T', 0, 'C', 0);
    $pdf->Cell(40,8,'MAC','L B T', 0, 'C', 0);
    $pdf->Cell(15,8,'Corte','L B T', 0, 'C', 0);
    $pdf->Cell(0, 8, 'Pago', 'L B T R', 1, 'C', 0);
    $pdf->Ln(1);
  }

  $pdf->Cell(70, 8, strtoupper($cliente['cliente_nombres'] . ' ' . $cliente['cliente_apellidos']), 0, 0, 'C', 1);
  $pdf->Cell(50,8,utf8_decode($cliente['nombre_colonia']), 0, 0, 'C', 1);
  $pdf->Cell(27,8,$cliente['cliente_telefono'], 0, 0, 'C', 1);
  $pdf->Cell(35,8,$cliente['cliente_ip'], 0, 0, 'C', 1);
  $pdf->Cell(40,8,$cliente['cliente_mac'], 0, 0, 'C', 1);
  $pdf->Cell(15,8,$cliente['dia_pago'], 0, 0, 'C', 1);
  $pdf->Cell(0,8,$cliente['mensualidad'], 0, 1, 'C', 1);
  $pdf->Ln(1);
}
$pdf->Output();



?>