<?php
  $body = Flight::request()->getBody();
  $request = json_decode($body, true);
  $mikrotik_id = $request['mikrotik_id'];
  // Flight::json($request);

  if (!isset($request['mikrotik_id'])
    || $request['mikrotik_id'] == ''
    || !isset($request['command']) || 
    $request['command'] == '' || 
    !isset($request['route']) || 
    $request['route'] == ''
  ) {

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
        $conn_mikrotik->write($request['route'] . "print", true);
        $array = $conn_mikrotik->read();

        for ($i = 0; $i < count($array); $i++) {

          if (isset($request['list']) 
            && $array[$i]['.id'] == $request['id'] 
            && $array[$i]['list'] == $request['list']
          ) {
            $request['props'][0] .= $i;
            break;
          }

          if ($array[$i]['.id'] == $request['id']) {
            $request['props'][0] .= $i;
            break;
          }
        }

        $conn_mikrotik->write($request['route'] . $request['command'], false); 
        $conn_mikrotik->write($request['props'][0], true);
        Flight::json(
          array(
            "status" => 200,
            "title" => "Datos encontrados!",
            "data" => $conn_mikrotik->read()
          )
        );                
        $conn_mikrotik->disconnect();
      }
    }

  }
?>




<?php

  // // $params = Flight::request()->data;
  // $body = Flight::request()->getBody();
  // $request = json_decode($body, true);
  
  // if (!isset($request['enterprice']) || 
  //   $request['enterprice'] == '' || 
  //   !isset($request['mikrotik']) || 
  //   $request['mikrotik'] == '' || 
  //   !isset($request['command']) || 
  //   $request['command'] == '' || 
  //   !isset($request['route']) || 
  //   $request['route'] == '' ||
  //   ($request['command'] != "remove")
  // ) {

  //   Flight::json(
  //     array(
  //       "status" => 504,
  //       "title" => "Bad Request!",
  //       "details" => "Los datos enviados son incorrectos!"
  //     )
  //   );

  // }  else {

  //   $enterprice = $request['enterprice'];
  //   $mikrotik = $request['mikrotik']; 
  //   $existSQL = "SELECT dbname, dbuser, dbpassword FROM empresas WHERE id = $enterprice";
  //   $query = Flight::gnconn()->prepare($existSQL);
  //   $query->execute();
  //   $rows_enterprice = $query->fetchAll();

  //   if (count($rows_enterprice) == 0) {
  //     Flight::json(
  //       array(
  //         "status" => 204,
  //         "title" => "Not Content!",
  //         "details" => "La empresa no existe!"
  //       )
  //     );
  //   }
    
  //   if (count($rows_enterprice) > 0) {

  //     $dbname = $rows_enterprice[0]['dbname'];
  //     $dbpass = $rows_enterprice[0]['dbpassword'];
  //     $dbuser = $rows_enterprice[0]['dbuser'];

  //     Flight::register('conn', 'PDO', array('mysql:host=localhost;dbname='.$dbname, $dbuser, $dbpass));
  //     $SQL = "SELECT mikrotik_address, mikrotik_user, mikrotik_password, mikrotik_port FROM mikrotik WHERE mikrotik_id = $mikrotik";
  //     $query = Flight::conn()->prepare($SQL);
  //     $query->execute();
  //     $rows_mikrotik = $query->fetchAll();

  //     if (count($rows_mikrotik) == 0) {
  //       Flight::json(
  //         array(
  //           "status" => 204,
  //           "title" => "Not Content!",
  //           "description" => "No fue posible encontrar los datos",
  //           "details" => "Revise la información enviada"
  //         )
  //       );
  //     }
      
  //     if (count($rows_mikrotik) > 0) {
  //       $address = $rows_mikrotik[0]['mikrotik_address'];
  //       $user = $rows_mikrotik[0]['mikrotik_user'];
  //       $password = $rows_mikrotik[0]['mikrotik_password'];
  //       $conn_mikrotik = new Mikrotik($address, $user, $password);

  //       if (!$conn_mikrotik) {
  //         Flight::json(
  //           array(
  //             "status" => 200,
  //             "title" => "Conexion erronea!",
  //             "description" => "La conexion con mikrotik ha fracazado!",
  //             "details" => "Revise que los datos de conexion sean correctos."
  //           )
  //         );
  //       }

  //       if ($conn_mikrotik->connect()) {
  //         $conn_mikrotik->write($request['route'] . "print", true);
  //         $array = $conn_mikrotik->read();

  //         for ($i = 0; $i < count($array); $i++) {
  //           if ($array[$i]['.id'] == $request['id']) {
  //             $request['props'][0] .= $i;
  //             break;
  //           }
  //         }

  //         $conn_mikrotik->write($request['route'] . $request['command'], false); 
  //         $conn_mikrotik->write($request['props'][0], true);
  //         Flight::json(
  //           array(
  //             "status" => 200,
  //             "title" => "Datos encontrados!",
  //             "data" => $conn_mikrotik->read()
  //           )
  //         );                
  //         $conn_mikrotik->disconnect();
  //       }

  //     } 

  //   }
    
  // }

?>
