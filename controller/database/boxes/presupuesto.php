<?php

	$SQL = "SELECT * FROM presupuesto WHERE presupuesto_status = 1 AND periodo_id = '$periodo_id'";
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