<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user || !$is_root) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ($is_user && $is_root) {
    $cologne = new Cologne();
    $cologne->enable($colonia_id);

    if ($cologne->conflict) {
        Flight::json([
            "status" => 409,
            "title" => "Conflict!",
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
            "details" => "Colonia habilitada correctamente!",
            "data" => $cologne->get_all_colognes()
        ]);
    }
}