<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);
if (!$is_user || !$is_root) {
    Flight::halt(401);
} 

if ($is_user && $is_root) {
    $config = new Brand();
    $config->disable_config($id);
    
    if ($config->error) {
        Flight::halt(500, $config->error_message);
    } 
    
    if (!$config->error) {
        Flight::json([
            "status" => 200,
            "title" => "Correcto!",
            "details" => "Configuracion guardada correctamente!",
            "data" => []
        ]);
    }
}