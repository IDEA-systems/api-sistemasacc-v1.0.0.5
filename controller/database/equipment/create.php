<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}


if ($is_user) {
    $request = Flight::request()->data;
    $valid_name = isset($request->name) || !empty($request->name);
    $valid_brand = isset($request->brand) || !empty($request->brand);
    $valid_model = isset($request->model) || !empty($request->model);
    
    if (!$valid_name || !$valid_brand || !$valid_model) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "Nombre, Marca y Modelo son requeridos!"
        ]);
    }

    if ($valid_name && $valid_brand && $valid_model) {
        $equipment = new Equipment($request);
        $equipment->create_new_equipment();

        // Exists Conflict validation?
        if ($equipment->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $equipment->error_message
            ]);
        }

        // Exists error insert
        if ($equipment->error) {
            Flight::json([
                "status" => 409,
                "title" => "Error interno!",
                "details" => $equipment->error_message
            ]);
        }

        // Not exists errors
        if (!$equipment->conflict && !$equipment->error) {
            Flight::json([
                "status" => 200,
                "title" => "Creado correctamente!",
                "details" => "Los datos fueron agregados correctamente!",
                "data" => $equipment->id
            ]);
        }
    }
}