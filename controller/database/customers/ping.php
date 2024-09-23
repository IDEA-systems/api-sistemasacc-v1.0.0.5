<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $customers = new ReadCustomers();
    $customers->ping($cliente_id);
    Flight::json([
        "status" => 200,
        "title" => "Proceso terminado!",
        "details" => "Se realizo el proceso correctamente!",
        "data" => $customers->mikrotik
    ]);
}