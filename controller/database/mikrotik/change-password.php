<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $request = Flight::request()->data;
    $mikrotik = new Mikrotiks($request);
    $mikrotik->change_password();
    
    if ($mikrotik->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error interno!",
            "details" => $mikrotik->error_message
        ]);
    }

    if (!$mikrotik->error) {
        Flight::json([
            "status" => 200,
            "title" => "Correcto!",
            "details" => "Contraseña cambiada correctamente!"
        ]);
    }
}