<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(412, "No autorizado!");
}

if ($is_user) {
    $services = new Services();
    $services->reactive($servicio_id);

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
            "details" => "El servicio se actualizo correctamente!",
            "data" => $services->get_all_services()
        ]);
    }
}