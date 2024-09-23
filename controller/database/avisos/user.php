<?php

	$SQL = "SELECT * FROM avisos WHERE destinatario = '$usuario_id' AND status_aviso = 1 ORDER BY importancia ASC";
	$query = Flight::gnconn()->prepare($SQL);
	$query->execute();
	$rows = $query->fetchAll();
	Flight::json(
		array(
			"status" => 200,
			"title" => "¡Datos encontrados!",
			"details" => "¡Más de una fila encontrada!",
			"data" => $rows
		)
	);
?>