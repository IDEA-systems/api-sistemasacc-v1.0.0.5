<?php

  require('../../sacc/assets/fpdf/fpdf.php');
  include_once('../../dbconfig/connect.php');


  class PDF extends FPDF {
    // Cabecera de página
    function Header()  {
        // Imagen de encabezado
        $this->Image('../../sacc/assets/images/brand.png',10,-3,30);
        // Logo
        $this->Image('../../sacc/assets/images/fondo-header.png',120,5,80, 15);
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
        $this->Cell(70,6,'INTERNET SOFTWARE AND INFORMATIC SERVICES',0,0,'R');
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
        $this->MultiCell(0,4, 'Avenida Lázaro Cárdenas, Colonia Centro, Cárdenas Tabasco, frente a Bodega Aurrera planta alta, local n° 1');
    }
  }

  // Creación del objeto de la clase heredada
  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddFont('Manrope-Regular','I', 'Manrope-Regular.php');
  $pdf->AddFont('Lilex-Medium','I', 'Lilex-Medium.php');
  $pdf->AddFont('Lilex-Bold','I', 'Lilex-Bold.php');
  $pdf->AddFont('Lilex','I', 'Lilex.php');
  $pdf->AddFont('Roboto-Bold','I', 'Roboto-Bold.php');
  $pdf->SetFont('Manrope-Regular', 'I', 8);
  $pdf->setTitle('Reporte de falla', false);
  // $pdf->SetMargins(12, 12, 12);
  // $pdf->SetTopMargin(0);
  $pdf->AddPage();
  $reporte_id = isset($_GET['reporte_id']) ? $_GET['reporte_id'] : false;
  $query = "SELECT * FROM reportes_fallas CROSS JOIN clientes ON clientes.cliente_id = reportes_fallas.cliente_id INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id INNER JOIN colonias ON clientes_servicios.colonia = colonias.colonia_id WHERE reportes_fallas.reporte_id = $reporte_id";

  $runQuery = mysqli_query($SQL, $query);
  if ($runQuery -> num_rows) {
    
    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(0, 10, 'REPORTE DE FALLAS EN EL SERVICIO' , 'L R T', 1, 'C', 0);
    
    $array = mysqli_fetch_assoc($runQuery);
    $folio = $array['reporte_id'] < 9 &&  $array['reporte_id'] < 10 ? '#000' . $array['reporte_id'] : '#00' . $array['reporte_id'];
    $material = isset($array['material']) ? $array['material'] : "";
    $procesos = isset($array['procesos']) ? $array['procesos'] : "";
    $acciones = $material . $procesos;
    
    // Titulo de la colunma mostrada //
    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(15, 8, 'Folio', 'L T B', 0, 'C', 0);
    $pdf->Cell(20, 8, 'Telefono', 'L T B', 0, 'C', 0);
    $pdf->Cell(30, 8, 'Fecha', 'L T B', 0, 'C', 0);
    $pdf->Cell(65, 8, 'Cliente', 'L T B', 0, 'C', 0);
    $pdf->Cell(0, 8, 'Colonia', 'L T B R', 1, 'C', 0);
    // Datos por arriba de la linea //
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->Cell(15, 7, $folio , 'L B', 0, 'C', 0);
    $pdf->Cell(20, 7, $array['cliente_telefono'] , 'L B', 0, 'C', 0);
    $pdf->Cell(30, 7, $array['fecha_captura'] , 'L B', 0, 'C', 0);
    $pdf->Cell(65, 7, utf8_decode($array['cliente_nombres']) . ' ' . $array['cliente_apellidos'] , 'L B', 0, 'C', 0);
    $pdf->Cell(0, 7, utf8_decode($array['nombre_colonia']) , 'L B R', 1, 'C', 0);

    $pdf->Ln(5);
    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(20, 8, 'IPv4', 'L T B', 0, 'C', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->Cell(30, 8, $array['cliente_ip'], 'L T B', 0, 'C', 0);
    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(20, 8, 'MAC', 'L T B', 0, 'C', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->Cell(40, 8, $array['cliente_mac'], 'L T B', 0, 'C', 0);
    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(30, 8, 'Antena AP', 'L T B', 0, 'C', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->Cell(50, 8, $array['cliente_ap'], 'L B R T', 1, 'C', 0);
    // $pdf->Cell(0, 8, 'Colonia', 'L T B R', 1, 'C', 0);
    // $pdf->Cell(0, 7, "IPv4: " . $array['cliente_ip'] . " | MAC: " . " | AP: " . $array['cliente_ap'] , 'L B R', 1, 'C', 0);

    $pdf->Ln(5);

    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Referencia del domicilio'), 0, 1, 'L', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->MultiCell(0, 5, utf8_decode($array['reporte_referencia'])  , 0, 'L');

    $pdf->Ln(5);

    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Domicilio del cliente'), 0, 1, 'L', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->MultiCell(0, 5, utf8_decode($array['reporte_domicilio']) , 0, 'L');

    $pdf->Ln(5);

    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Descripción del reporte'), 0, 1, 'L', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->MultiCell(0, 5, utf8_decode($array['reporte_descripcion']) , 0, 'L');

    $pdf->Ln(10);

    $pdf->SetFillColor(16, 185, 129);
    $pdf->SetFont('Lilex-Bold', 'I', 8);
    $pdf->Cell(5, 6, '', 1, 0, 'C', $array['cliente_activo']);
    $pdf->Cell(35, 6, 'ACTIVO EN EL SACC', 'T B R', 0, 'C', 0);
    $pdf->Cell(5, 6, '', 0, 0, 'C', 0);

    $pdf->SetFont('Lilex-Bold', 'I', 8);
    $pdf->Cell(5, 6, '', 1, 0, 'C', $array['activo_mikrotik']);
    $pdf->Cell(35, 6, 'ACTIVO EN MIKROTIK', 'T B R', 0, 'C', 0);
    $pdf->Cell(5, 6, '', 0, 0, 'C', 0);

    $pdf->SetFont('Lilex-Bold', 'I', 8);
    $pdf->Cell(5, 6, '', 1, 0, 'C', $array['cliente_morosos']);
    $pdf->Cell(20, 6, 'MOROSO', 'L T B R', 0, 'C', 0);
    $pdf->Cell(5, 6, '', 0, 0, 'C', 0);

    $pdf->SetFont('Lilex-Bold', 'I', 8);
    $pdf->Cell(5, 6, '', 1, 0, 'C', $array['atencion_online']);
    $pdf->Cell(35, 6, 'SOPORTE BRINDADO', 'T B R', 0, 'C', 0);
    $pdf->Cell(5, 6, '', 0, 0, 'C', 0);

    $pdf->SetFont('Lilex-Bold', 'I', 8);
    $pdf->Cell(5, 6, '', 1, 0, 'C', $array['cliente_mikrotik']);
    $pdf->Cell(20, 6, 'ONLINE', 'T B R', 0, 'C', 0);
    $pdf->Cell(5, 6, '', 0, 1, 'C', 0);

    $pdf->Ln(10);
    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(0, 10, 'DATOS DE SOLUCION' , 'T L R', 1, 'C', 0);

    $pdf->Cell(60, 8, utf8_decode('Técnico'), 'L T B', 0, 'C', 0);
    $pdf->Cell(50, 8, utf8_decode('Fecha de finalización'), 'L T B', 0, 'C', 0);
    $pdf->Cell(0, 8, 'Vehiculo', 'L T B R', 1, 'C', 0);
    
    // Datos por arriba de la linea //
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->Cell(60, 10, isset($array['tecnico']) ? $array['tecnico'] : "", 'L B', 0, 'C', 0);
    $pdf->Cell(50, 10, isset($array['fecha_finalizacion']) ? $array['fecha_finalizacion'] : "", 'L B', 0, 'C', 0);
    $pdf->Cell(0, 10, isset($array['vehiculo']) ? $array['vehiculo'] : "", 'L B R', 1, 'C', 0);

    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(60, 40, 'Acciones y material', 'L T', 0, 'C', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->Cell(0, 40, $acciones, 'L R B', 1, 'C', 0);

    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(60, 20, 'Anexos', 'L T B', 0, 'C', 0);
    $pdf->SetFont('Lilex', 'I', 8);
    $pdf->Cell(0, 20, isset($array['anexos']) ? $array['anexos'] : "", 'L R B', 1, 'C', 0);

    $pdf->Ln(25);

    $pdf->SetFont('Lilex-Bold', 'I', 10);
    $pdf->Cell(60, 8, 'Efectivo', 'T', 0, 'C', 0);
    $pdf->Cell(5, 8, "", 0, 0, 'C', 0);
    $pdf->Cell(60, 8, 'Firma del cliente', 'T', 0, 'C', 0);
    $pdf->Cell(5, 8, '', 0, 0, 'C', 0);
    $pdf->Cell(0, 8, utf8_decode('Firma del técnico'), 'T', 1, 'C', 0);

  }

  $pdf->output();

?>