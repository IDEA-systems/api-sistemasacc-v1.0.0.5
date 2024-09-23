<?php

$request = Flight::request()->data;

$invalid_name = !isset($request->nombre) ||
    empty($request->nombre);

$invalid_ip = !isset($request->address) ||
    empty($request->address);

$invalid_model = !isset($request->modelo) ||
    empty($request->modelo);

$invalid_serie = !isset($request->serie) ||
    empty($request->serie);

$bad_request = $invalid_name ||
    $invalid_ip ||
    $invalid_serie ||
    $invalid_model;

if ($bad_request) {
    Flight::json([
        "status" => 400,
        "title" => "Conflicto!",
        "details" => "Nombre, Modelo, Serie y Address son requeridos!"
    ]);
}

if (!$bad_request) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(412, "No autorizado!");
    }
    
    if ($is_user) {
        $olt = new Olt($request);
        $olt->create();
    
        if ($olt->error) {
            Flight::halt(500, $olt->error_message);
        }
    
        else if ($olt->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $olt->error_message
            ]);
        }
    
        else {
            Flight::json([
                "status" => 200,
                "title" => "OLT Agregada",
                "details" => "La OLT fue agregada correctamente",
                "data" => $olt->get_olt_by_id(),
                "olts" => $olt->get_all_olt()
            ]);
        }
    }
}