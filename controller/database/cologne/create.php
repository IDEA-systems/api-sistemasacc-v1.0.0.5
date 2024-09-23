<?php

$request = Flight::request()->data;

$invalid_name = !isset($request->nombre_colonia)
    || $request->nombre_colonia == '';

$invalid_mikrotik = !isset($request->mikrotik_control) ||
    $request->mikrotik_controll = '';

$bad_request = $invalid_name || 
    $invalid_mikrotik;

if ($bad_request) {
    Flight::halt(400, "Bad request!");
}

if (!$bad_request) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    }

    if ($is_user) {
        $cologne = new Cologne($request);
        $cologne->Create();

        if ($cologne->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto",
                "details" => $cologne->error_message
            ]);
        }

        else if ($cologne->error) {
            Flight::halt(500, $cologne->error_message);
        }

        else {
            Flight::json([
                "status" => 200,
                "title" => "Correcto",
                "details" => "Colonia agregada correctamente!",
                "data" => $cologne->get_all_colognes(),
                "colonia" => $cologne->get_cologne_by_id()[0]
            ]);
        }
    }
}