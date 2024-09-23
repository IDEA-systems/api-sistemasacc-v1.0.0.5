<?php
$body = Flight::request()->getBody();
$request = json_decode($body, true);
$mikrotik_id = $request[ 'mikrotik_id' ];
// Flight::json($request);

if (!isset($request[ 'mikrotik_id' ]) || !isset($request[ 'command' ]) || $request[ 'command' ] == '' || !isset($request[ 'route' ]) || $request[ 'route' ] == '' || !isset($request[ 'props' ]) || count($request[ 'props' ]) == 0) {
    Flight::json([ 
        "status" => 504,
        "title" => "Bad Request!",
        "details" => "Para agregar necesitas enviar datos!"
    ]);
} 

else {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::halt(401, "Usuario sin permisos!");
    }

    if ($is_user) {
        $mikrotik = new Mikrotiks();
        $mikrotik->mikrotik_id = $request["mikrotik_id"];
        $rows = $mikrotik->get_by_id_all_data();

        $address = $rows[0]['mikrotik_ip'];
        $user = $rows[0]['mikrotik_usuario'];
        $password = $rows[0]['mikrotik_password'];
        $puerto = $rows[0]['mikrotik_puerto'];
        $conn = new Mikrotik($address, $user, $password, $puerto);

        if (!$conn->connected) {
            Flight::halt(500, "Error interno con mikrotik!");
        }
        if ($conn->connected) {
            $conn->write($request['route'] . $request['command'], false);
            $data = array_slice($request['props'], 0, -1);
            $last_data = array_slice($request['props'], -1);

            for ($i = 0; $i < count($data); $i++) {
                $conn->write($data[$i], false);
            }

            $conn->write($last_data[0], true);
            $rows = $conn->read();

            Flight::json([
                "status" => 200,
                "title" => "Datos encontrados!",
                "details" => "Datos agregados correctamente!",
                "data" => $rows
            ]);
            
            $conn->disconnect();
        }
    }
    // $SQL = "SELECT mikrotik_ip, mikrotik_usuario, mikrotik_password, mikrotik_puerto FROM mikrotiks WHERE mikrotik_id = $mikrotik_id";
    // $query = Flight::gnconn()->prepare($SQL);
    // $query->execute();
    // $rows_mikrotik = $query->fetchAll();
    // if (count($rows_mikrotik) == 0) {
    //     Flight::json(
    //         array(
    //             "status" => 204,
    //             "title" => "Not Content!",
    //             "details" => "No fue posible encontrar los datos del mikrotik especificado!",
    //         )
    //     );
    // }
    // if (count($rows_mikrotik) > 0) {
    //     $address = $rows_mikrotik[ 0 ][ 'mikrotik_ip' ];
    //     $user = $rows_mikrotik[ 0 ][ 'mikrotik_usuario' ];
    //     $password = $rows_mikrotik[ 0 ][ 'mikrotik_password' ];
    //     $puerto = $rows_mikrotik[ 0 ][ 'mikrotik_puerto' ];
    //     $conn_mikrotik = new Mikrotik($address, $user, $password, $puerto);
    //     if (!$conn_mikrotik) {
    //         Flight::json(
    //             array(
    //                 "status" => 200,
    //                 "title" => "Conexion erronea!",
    //                 "details" => "La conexion con mikrotik ha fracazado!"
    //             )
    //         );
    //     }

    //     if ($conn_mikrotik->connect()) {
    //         $conn_mikrotik->write($request[ 'route' ] . $request[ 'command' ], false);
    //         $data = array_slice($request[ 'props' ], 0, -1);
    //         $last_data = array_slice($request[ 'props' ], -1);
    //         for ($i = 0; $i < count($data); $i++) {
    //             $conn_mikrotik->write($data[ $i ], false);
    //         }
    //         $conn_mikrotik->write($last_data[ 0 ], true);
    //         Flight::json(
    //             array(
    //                 "status" => 200,
    //                 "title" => "Datos encontrados!",
    //                 "details" => "Datos agregados correctamente!",
    //                 "data" => $conn_mikrotik->read()
    //             )
    //         );
    //         $conn_mikrotik->disconnect();
    //     }
    // }

}