<?php 
  // Año actual //
  $year = date('Y');


  /***************************
  * PRIMER RECURSO OBTENIDO *
  ***************************/
	$SQL = "
    SELECT SUM(monto_movimiento) 
    AS directas_mes 
    FROM `movimientos_cajas` 
    WHERE MONTH(fecha_movimiento) = MONTH(CURRENT_DATE()) 
    AND YEAR(fecha_movimiento) = $year 
    AND tipo_movimiento = 1 
    AND pago_id 
    IS 
    NULL
  ";

	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$entradas_directas = $query->fetchAll();


  /******************
   * SEGUNDO RECURSO *
   ******************/
  $year = date('Y');
	$SQL = "
    SELECT SUM(monto_movimiento)
    AS entradas_mes 
    FROM `movimientos_cajas` 
    WHERE MONTH(fecha_movimiento) = MONTH(CURRENT_DATE()) 
    AND YEAR(fecha_movimiento) = $year
    AND tipo_movimiento = 1
  ";

	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$entradas_mes = $query->fetchAll();


  /******************
   * TERCER RECURSO *
   ******************/
  $SQL = "
    SELECT SUM(costo_renta + mensualidad) 
    AS esperado_quince 
    FROM clientes_servicios 
    WHERE cliente_corte = 1
  ";

	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$esperado_quince = $query->fetchAll();

  $SQL = "
    SELECT SUM(costo_renta + mensualidad) 
    AS esperado_treinta 
    FROM clientes_servicios 
    WHERE cliente_corte = 1
  ";

	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$esperado_treinta = $query->fetchAll();


  /******************
   * CUARTO RECURSO *
   ******************/
  $SQL = "SELECT saldo_actual FROM `caja_fisica`";
	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$caja_fisica = $query->fetchAll();


  Flight::json(
    array(
      $entradas_directas,
      $entradas_mes,
      $esperado_quince,
      $esperado_treinta,
      $caja_fisica
    )
  );

?>