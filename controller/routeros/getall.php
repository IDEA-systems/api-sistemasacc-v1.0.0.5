<?php

// $params = Flight::request()->data;
$body = Flight::request()->getBody();
$request = json_decode($body, true);

$commands = ["getall", "print", "once"];

$invalid_id = !isset($request['mikrotik_id']) ||
    $request['mikrotik_id'] == '';

$invalid_command = !isset($request['command']) ||
    $request['command'] == '' ||
    !in_array($request['command'], $commands);

$bad_request = $invalid_id || $invalid_command;

if ($bad_request) {
    Flight::json([ 
        "status" => 400,
        "title" => "Bad Request!",
        "details" => "Los datos enviados son incorrectos!"
    ]);

} 

if (!$bad_request) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::halt(401, "Usuario no autorizado!");
    }

    if ($is_user) {
        $mikrotik = new Mikrotiks();
        $mikrotik->mikrotik_id = $request["mikrotik_id"];
        $rows = $mikrotik->get_by_id_all_data();

        if (count($rows) == 0) {
            Flight::json([]);
        }

        if (count($rows) > 0) {
            $address = $rows[0]['mikrotik_ip'];
            $user = $rows[0]['mikrotik_usuario'];
            $password = $rows[0]['mikrotik_password'];
            $puerto = $rows[0]['mikrotik_puerto'];
            $conn = new Mikrotik($address, $user, $password, $puerto);

            if (!$conn->connected) {
                Flight::halt(500, "Mikrotik connection error!");
            }

            if ($conn->connected) {
                $conn->write($request['route'] . $request['command']);
                $data = $conn->read();
                Flight::json($data);
                $conn->disconnect();
            }
        }
    }
}