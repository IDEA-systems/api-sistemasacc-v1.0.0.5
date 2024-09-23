<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(500, "Error interno");
}

if ($is_user) {
    $mikrotik = new ReadMikrotik();
    $data = $mikrotik->get_mikrotik_by_id($mikrotik_id);

    // No se encontro ningun mikrotik
    if (empty($data)) {
        Flight::halt(500, "Error interno");
    }

    // Si se encontro, conectar
    if (!empty($data)) {
        $conn = new Mikrotik(
            $data[0]["mikrotik_ip"],
            $data[0]["mikrotik_usuario"],
            $data[0]["mikrotik_password"],
            $data[0]["mikrotik_puerto"]
        );

        if (!$conn->connected) {
            Flight::halt(500, "Error interno");
        }

        if ($conn->connected) {
            $conn->write("/interface/monitor-traffic", false);
            $conn->write("=interface=$interface", false);
            $conn->write("=once=");
            $array = $conn->read();
            Flight::json($array);
        }
    }
}



// $params = Flight::request()->data;
//   $body = Flight::request()->getBody();
//   $request = json_decode($body, true);

//   if (!isset($request['mikrotik_id']) || $request['mikrotik_id'] == "") {
//     Flight::json(
//       array(
//         "status" => 504,
//         "title" => "Bad Request!",
//         "details" => "Los datos enviados son incorrectos!"
//       )
//     );
//   } else {
//     $SQL = "SELECT mikrotik_ip, mikrotik_usuario, mikrotik_password, mikrotik_puerto FROM mikrotiks WHERE mikrotik_id = " . $request['mikrotik_id'];
//     $query = Flight::gnconn()->prepare($SQL);
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
//       $address = $rows_mikrotik[0]['mikrotik_ip'];
//       $user = $rows_mikrotik[0]['mikrotik_usuario'];
//       $password = $rows_mikrotik[0]['mikrotik_password'];
//       $puerto = $rows_mikrotik[0]['mikrotik_puerto'];
//       $conn_mikrotik = new Mikrotik($address, $user, $password, $puerto);

//       if ($conn_mikrotik->connect()) {
//         $conn_mikrotik->write("/interface/monitor-traffic", false);
//         $conn_mikrotik->write('=interface=' . $interface, false);
//         $conn_mikrotik->write($request['once'], true);
//         $data = $conn_mikrotik->read();

//         if (count($data) > 0) {

//           Flight::json(
//             array(
//               "status" => 200,
//               "data" => $data
//             )
//           );

//         } else {

//           Flight::json(
//             array(
//               "status" => 204,
//               "title" => "Datos no encontrados!",
//               "data" => []
//             )
//           );

//         }

//         $conn_mikrotik->disconnect();

//       }
//     }

//   }
// ?>