<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user || !$is_root) {
    Flight::halt(401);
}

if ($is_user && $is_root) {
    $package = new Packages();
    $package->delete_package($idpaquete);
    
    if ($package->error) {
        Flight::json([
            "status" => 409,
            "title" => "Conflicto",
            "details" => $package->error_message
        ]);
    }

    if (!$package->error) {
        $rows = $package->get_all_packages();
        Flight::json([
            "status" => 200,
            "title" => "Correcto",
            "details" => "El paquete ha sido eliminado!",
            "data" => $rows
        ]);
    }
}