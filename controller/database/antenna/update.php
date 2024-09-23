<?php

$request = Flight::request()->data;

$invalid_model = !isset($request->modelo)
    || $request->modelo == '';

$invalid_marca = !isset($request->marca)
    || $request->marca == '';

$bad_request = $invalid_model || $invalid_marca;

if ($bad_request) {
    Flight::json([
        "status" => 400,
        "title" => "Solicitud incorrecta!",
        "details" => "Modelo y Marca son rerqueridos!"
    ]);
}

if (!$bad_request) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::json([
            "status" => 401,
            "title" => "Usuario no autorizado!",
            "details" => "No tiene permisos para esta acción!"
        ]);
    }

    if ($is_user) {
        $files = Flight::request()->files;
        $antenna = new Antenna($request, $files);
        $antenna->update();

        if ($antenna->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $antenna->error_message
            ]);
        }

        else if ($antenna->error) {
            Flight::json([
                "status" => 500,
                "title" => "Conflicto!",
                "details" => $antenna->error_message
            ]);
        }

        else {
            $byid = $antenna->get_antenna_by_id();
            $antennas = $antenna->get_all_antennas();
            $antenna->save_img($files->fotoantena);
            Flight::json([
                "status" => 200,
                "title" => "Actualizado!",
                "details" => "Antena actualizada correctamente",
                "antennas" => $antennas,
                "data" => $byid
            ]);
        }
    }
}