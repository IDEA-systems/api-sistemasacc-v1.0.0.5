<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $request = Flight::request()->data;
    $mikrotik = new Mikrotiks($request);
    $mikrotik->retry_add_config();
    Flight::json([
        "redirect" => $mikrotik->redirect,
        "accept" => $mikrotik->accept,
        "drop" => $mikrotik->drop
    ]);
}