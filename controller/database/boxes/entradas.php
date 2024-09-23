<?php

  $year = date('Y');
	$SQL = "SELECT SUM(monto_movimiento)
    AS entradas_mes 
    FROM `movimientos_cajas` 
    WHERE MONTH(fecha_movimiento) = MONTH(CURRENT_DATE()) 
    AND YEAR(fecha_movimiento) = $year
    AND tipo_movimiento = 1
  ";

	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$rows = $query->fetchAll();

	if (count($rows) == 0) {

		Flight::json(array(
			"status" => 204,
			"title" => "¡Datos vacios!",
			"details" => "¡No encotramos datos!",
			"data" => []
		));

	} else {

		Flight::json(array(
			"status" => 200,
			"title" => "¡Datos encontrados!",
			"details" => "¡Más de una fila encontrada!",
			"data" => $rows
		));

	}


?>