<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user || !$is_root) {
    Flight::halt(401, "Usuario no autorizado!");
} 

if ($is_user && $is_root) {
    $request = Flight::request()->data;
    $config = new Brand($request);
    $config->update_messages();

    if ($config->error) {
        Flight::halt(500);
    }

    if (!$config->error) {
        Flight::json([
            "status" => 200,
            "title" => "Correcto!",
            "details" => "Los mensajes fueron guardados correctamente!",
            "data" => []
        ]);
    }
}