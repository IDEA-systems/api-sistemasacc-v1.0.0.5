<?php

  $year = date('Y');
	$SQL = "SELECT SUM(costo_renta + mensualidad) AS quince FROM clientes_servicios WHERE cliente_corte = $corte_id";

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