<?php

$periodo_id = isset($_GET['periodo_id']) ? $_GET['periodo_id'] : '';
$fecha_periodo = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$fin = isset($_GET['fin']) ? $_GET['fin'] : '';

require('../../sacc/assets/fpdf/fpdf.php');
$conn = mysqli_connect("162.241.61.124", "isisinte_tisacc", "ideadevcode135", "isisinte_sacctest");

// Creación del objeto de la clase heredada
$pdf = new FPDF();
$pdf->AddPage('P', 'A4');
$pdf->AddFont('Manrope-Regular','I', 'Manrope-Regular.php');
$pdf->AddFont('Lilex-Medium','I', 'Lilex-Medium.php');
$pdf->AddFont('Lilex-Bold','I', 'Lilex-Bold.php');
$pdf->AddFont('Lilex','I', 'Lilex.php');
$pdf->AddFont('Roboto-Bold','I', 'Roboto-Bold.php');
$pdf->SetFont('Lilex','I',8);
// $pdf->SetMargins(0,0,0);


$pdf->SetTitle('Notas de pago para el periodo: ' . $periodo_id);
$i = 0;
foreach ($clientes as $cliente) {

  $i += 1;

  if ($i <= 9) {
    $nota = '#00' . $i;
  } else if ($i > 9 && $i < 99) {
    $nota = '#0' . $i ;
  } else {
    $nota = '#' . $i;
  }


  $pdf->SetDrawColor(203, 213, 225);
  $pdf->SetLineWidth(0.1);

  $pdf->Cell(0,1,'',0);
  $pdf->Ln(2.5);
  $pdf->Cell(50, 2, '', 0, 0);
  $pdf->Cell(0, 2, '', 0, 1);

  $pdf->Image('../../sacc/assets/images/brand.png', $pdf->GetX() , $pdf->GetY(), 45);
  $pdf->Cell(2, 30, '', 0, 0);
  
  $pdf->Cell(50, 6, '' , '', 0);
  $pdf->MultiCell(0,6, utf8_decode('Internet Software y Servicios Informáticos. Oficinas: Avenida Lázaro Cárdenas, colonia centro, frente a Bodega Aurrera, planta alta local 1, Telefono: 93-71-65-97-27, RFC: GEMJ871227DD9, Código postal: 86500, Email: isisinternet@isisinternet.com, Web: www.isisinternet.com'), 'B' , 1);

  $pdf->Cell(50, 2, '', 0, 0);
  $pdf->Cell(0, 2, '', 0, 1);

  $pdf->SetFont('Lilex-Bold','I',10);
  $pdf->Cell(52, 8, ' Nombre del cliente :', 0, 0, 'L');

  $pdf->SetFont('Lilex','I',10);
  $pdf->Cell(60, 8, utf8_decode($cliente['cliente_nombres'] . ' ' . $cliente['cliente_apellidos']), 0, 0);
  $pdf->Cell(0,8, 'Nota: ' . $nota, 0, 1, 'R');
  
  $pdf->SetFont('Lilex-Bold','I',10);
  $pdf->Cell(52, 8, ' Colonia :', 0, 0, 'L');

  $pdf->SetFont('Lilex','I',10);
  $pdf->Cell(0, 8, utf8_decode($cliente['nombre_colonia']), 0, 1);

  $pdf->SetFont('Lilex-Bold','I',10);
  $pdf->Cell(52, 8, ' Concepto de pago :', 0, 0, 'L');

  $pdf->SetFont('Lilex','I',10);
  $pdf->Cell(0, 8, 'Servicios de internet', 0, 1);

  $pdf->SetFont('Lilex-Bold','I',10);
  $pdf->Cell(52, 8, ' Periodo pagado :', 0, 0, 'L');

  $pdf->SetFont('Lilex','I',10);
  $pdf->Cell(0, 8, $periodo_id . ' : ' . $fecha_periodo . ' al ' . $fin, 0, 1);

  $pdf->SetFont('Lilex-Bold','I',10);
  $pdf->Cell(52, 8, ' Monto del pago :', 0, 0, 'L');

  $pdf->SetFont('Lilex','I',10);
  $pdf->Cell(0, 8, '$' . number_format($cliente['mensualidad'],2) . ': ' . $cliente['nombre_paquete'], 0, 1);

  $pdf->SetFont('Lilex-Bold','I',10);
  $pdf->Cell(52, 8, ' Costo de renta : ', 0, 0, 'L');

  $pdf->SetFont('Lilex','I',10);
  $pdf->Cell(0, 8, '$' . number_format($cliente['costo_renta'],2) . ': Equipo ' . $cliente['nombre_status_equipo'], 0, 1);

  $pdf->SetFont('Lilex-Bold','I',10);
  $pdf->Cell(52, 8, ' Fecha :', 0, 0, 'L');

  $pdf->SetFont('Lilex','I',10);
  $pdf->Cell(0, 8, date('Y-m-d'), 0, 1);

  $pdf->SetFont('Lilex','I', 8);

  $pdf->Ln(2.5);

}

$pdf->Output();


?>