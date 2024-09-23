<?php

$request = Flight::request()->data;

$invalid_id = !isset($request->colonia_id)
    || $request->colonia_id == '';

$invalid_name = !isset($request->nombre_colonia)
    || $request->nombre_colonia == '';

$invalid_mikrotik = !isset($request->mikrotik_control) ||
    $request->mikrotik_controll = '';

$bad_request = $invalid_id || 
    $invalid_name || 
    $invalid_mikrotik;

if ($bad_request) {
    Flight::halt(400, "Bad request!");
}

if (!$bad_request) {
    // Verify user 
    $login = new Login();
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::halt(401, "Usuario no autorizado!");
    } 
    
    if ($is_user) {
        // Update cologne
        $cologne = new Cologne($request);
        $cologne->update();
        
        if ($cologne->conflict) {
            Flight::json([
                "status" => 309,
                "title" => "Conflicto!",
                "details" => $cologne->error_message
            ]);
        }

        else if ($cologne->error) {
            Flight::halt(500, $cologne->error_message);
        }

        else {
            Flight::json([
                "status" => 200,
                "title" => "Correcto!",
                "details" => "Colonia agregada correctamente!",
                "data" => $cologne->get_all_colognes()
            ]);
        }
    }
}