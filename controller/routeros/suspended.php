<?php

  $data = file_get_contents("controller/database/clientes/select-clients.json");
	$clientes = json_decode($data, true);

	foreach($clientes as $cliente) {

		$mikrotik_id = $cliente['mikrotik_control'];
		$cliente_id = $cliente['cliente_id'];

		$SQL = "SELECT mikrotik_ip, mikrotik_usuario, mikrotik_password, mikrotik_puerto FROM mikrotiks WHERE mikrotik_id = $mikrotik_id";
		$query = Flight::gnconn()->prepare($SQL);
		$query->execute();
		$rows = $query->fetchAll();
		$length = count($rows);
		$conn = false;

		if ( $length == 0 ) { continue; }
		
		if ( $length > 0 ) {
			$conn = new Mikrotik(
				$rows[0]['mikrotik_ip'], 
				$rows[0]['mikrotik_usuario'], 
				$rows[0]['mikrotik_password'], 
				$rows_mikrotik[0]['mikrotik_puerto']
			);
		}

		if ( !$conn ) { 
			$sql = "UPDATE clientes_servicios SET cliente_status = 2 WHERE cliente_id = '$cliente_id'";
			$query = Flight::gnconn()->prepare($sql);
			$query->execute();
			$cliente["cliente_status"] = 2;
			continue;
		}

		if ( $conn ) {
			$conn->write("/ip/firewall/address-list/add", false);
			$conn->write("=address=" . $cliente['cliente_ip'], false);
			$conn->write("=comment=" . $cliente['cliente_nombres'] . ' ' . $cliente['cliente_apellidos'], false);
			$conn->write("=list=MOROSOS", true);

			$sql = "UPDATE clientes_servicios SET cliente_status = 2 WHERE cliente_id = '$cliente_id'";
			$query = Flight::gnconn()->prepare($sql);
			$query->execute();

			$cliente["cliente_status"] = 2;
		}

	}

	Flight::json(
		array(
			"status" => 200,
			"title" => "¡Completado!",
			"details" => "¡Se ha completado la operación!",
			"data" => $clientes
		)
	);
?>