<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(412, "No autorizado!");
}

if ($is_user) {
    $request = Flight::request()->data;
    $invalid_name = !isset($request->nombre_servicio) 
        || empty($request->nombre_servicio);

    if ($invalid_name) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "Nombre de servicios y Costo de servicio obligatorios!"
        ]);
    }

    if (!$invalid_name) {
        $services = new Services($request);
        $services->create();

        if ($services->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $services->error_message
            ]);
        }

        if ($services->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $services->error_message
            ]);
        }

        if (!$services->conflict && !$services->error) {
            Flight::json([
                "status" => 200,
                "title" => "Agregado!",
                "details" => "El servicio se agrego correctamente!",
                "data" => $services->get_all_services()
            ]);
        }
    }
}