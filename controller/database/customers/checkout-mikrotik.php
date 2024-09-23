<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $customer = new ReadCustomers();
    $customer->checkout_mikrotik($cliente_id);
    Flight::json([
        "status" => 200,
        "title" => "Encontrado!",
        "details" => "Más de una fila fue encontrada",
        "data" => $customer->mikrotik
    ]);
}