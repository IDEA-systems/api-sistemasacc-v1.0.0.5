<?php

$user = new Login();
$is_user = $user->is_user($usuario_id);

if (!$is_user) {
    Flight::json([
        "status" => 401,
        "title" => "No autorizado!",
        "details" => "El usuario no cuenta con permisos!"
    ]);
}

if ($is_user) {
    $install = new Instalations();
    
    $install->status = 4;
    $install->instalacion_id = $instalacion_id;

    $install->change_status_installation();

    if ($install->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error interno!",
            "details" => $install->error_message
        ]);
    }

    if (!$install->error) {
        Flight::json([
            "status" => 200,
            "title" => "Correcto!",
            "details" => "Instalacion eliminada correctamante!",
            "installation" => $install->get_instalation_byid($instalacion_id)
        ]);
    }
}