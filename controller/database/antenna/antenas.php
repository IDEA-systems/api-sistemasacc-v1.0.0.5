<?php

	$SQL = "SELECT * FROM antenas";

	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$rows = $query->fetchAll();

	Flight::json([
		"status" => 200,
		"title" => "¡Datos encontrados!",
		"details" => "¡Más de una fila encontrada!",
		"data" => $rows
	]);