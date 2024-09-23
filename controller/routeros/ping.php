<?php
  $body = Flight::request()->getBody();
  $request = json_decode($body, true);
  $mikrotik_id = $request['mikrotik_id'];

  if (!isset($request['mikrotik_id'])) {

    Flight::json(
      array(
        "status" => 504,
        "title" => "Bad Request!",
        "details" => "Los datos enviados son incorrectos!"
      )
    );

  } else {

    $SQL = "SELECT mikrotik_ip, mikrotik_usuario, mikrotik_password, mikrotik_puerto FROM mikrotiks WHERE mikrotik_id = $mikrotik_id";
    $query = Flight::gnconn()->prepare($SQL);
    $query->execute();
    $rows_mikrotik = $query->fetchAll();

    if (count($rows_mikrotik) == 0) {
      Flight::json(
        array(
          "status" => 204,
          "title" => "Not Content!",
          "description" => "No fue posible encontrar los datos",
          "details" => "Revise la información enviada"
        )
      );
    }

    if (count($rows_mikrotik) > 0) {      
      $address = $rows_mikrotik[0]['mikrotik_ip'];
      $user = $rows_mikrotik[0]['mikrotik_usuario'];
      $password = $rows_mikrotik[0]['mikrotik_password'];
      $puerto = $rows_mikrotik[0]['mikrotik_puerto'];
      $conn_mikrotik = new Mikrotik($address, $user, $password, $puerto);

      if (!$conn_mikrotik) {
        Flight::json(
          array(
            "status" => 200,
            "title" => "Conexion erronea!",
            "description" => "La conexion con mikrotik ha fracazado!",
            "details" => "Revise que los datos de conexion sean correctos."
          )
        );
      }

      if ($conn_mikrotik->connect()) {
        $conn_mikrotik->write($request['route'], false);
        $conn_mikrotik->write("=address=" . $ip, false);
        $conn_mikrotik->write("=count=3", false);
        $conn_mikrotik->write("=interval=1", true);
        Flight::json(
          array(
            "status" => 200,
            "title" => "Datos encontrados!",
            "address" => $ip,
            "data" => $conn_mikrotik->read()
          )
        );                
        $conn_mikrotik->disconnect();
      }
    }

  }
?>
