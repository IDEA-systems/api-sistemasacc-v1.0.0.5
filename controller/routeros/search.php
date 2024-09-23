<?php

// $params = Flight::request()->data;
$body = Flight::request()->getBody();
$request = json_decode($body, true);

$invalid_mikrotik = !isset($request['mikrotik_id']) ||
    $request['mikrotik_id'] == "";

$invalid_command = !isset($request['command']) ||
    $request['command'] == '' ||
    $request['command'] != "getall" &&
    $request['command'] != "print";

$invalid_route = !isset($request['route']) ||
    $request['route'] == "";

$bad_request = $invalid_mikrotik || 
    $invalid_command || 
    $invalid_route;


if ($bad_request) {
    Flight::json([
        "status" => 400,
        "title" => "Solicitud incorrecta!",
        "details" => "Mikrotik, Comando y Route son requeridos!"
    ]);
} 

if (!$bad_request) {
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
            Flight::halt(500, "Mikrotik connection error!");
        }

        if ($conn->connected) {
            $conn->write($request['route'] . $request['command'], true);
            $array = $conn->read();
            $data = [];
            for ($i = 0; $i < count($array); $i++) {
                $target = isset($array[$i]['target']) ? explode('/', $array[$i]['target'])[0] : false;
                $address = isset($array[$i]['address']) ? $array[$i]['address'] : false;
                $mac_address = isset($array[$i]['mac-address']) ? $array[$i]['mac-address'] : false;
                $src_address = isset($array[$i]['src-address']) ? explode(':', $array[$i]['src-address'])[0] : false;
                $local_address = isset($array[$i]['local-address']) ? $array[$i]['local-address'] : false;
                $remote_address = isset($array[$i]['remote-address']) ? $array[$i]['remote-address'] : false;
                $name = isset($array[$i]['name']) ? $array[$i]['name'] : false;
                $list = isset($array[$i]['list']) ? $array[$i]['list'] : false;

                if ($list && $address && $request['list'] && $address == $search_data && $list == $request['list']) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                    break;
                }

                if ($target && $target == $search_data) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                    break;
                }

                if ($address && !$list && $address == $search_data) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                    break;
                }

                if ($local_address && $local_address == $search_data) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                }

                if ($remote_address && $remote_address == $search_data) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                }

                if ($src_address && $src_address == $search_data) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                }

                if ($mac_address && $mac_address == $search_data) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                }

                if ($name && $name == $search_data) {
                    $data[0] = $array[$i];
                    $data[0]["index"] = $i;
                }

                if (!$name && !$mac_address && !$src_address && !$remote_address && !$local_address && !$address && !$target && !$list) {
                    $data = [];
                }
            }

            Flight::json($data);
            $conn->disconnect();
        }
    }
}
