<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $cliente = new Customer();
    $cliente->cliente_id = $cliente_id;
    $cliente->customer_enable();

    if ($cliente->error) {
        Flight::json([
            "status" => 500,
            "title" => "Incorrecto!",
            "details" => $cliente->error_message
        ]);
    }
    
    if (!$cliente->error) {
        Flight::json([
            "status" => 200,
            "title" => "Correcto!",
            "details" => "El cliente fue habilitado correctamente!",
            "customer" => $cliente->get_customer_by_id(),
            "mikrotik" => [
                "firewall" => $customer->firewall,
                "queues" => $customer->queues,
                "leases" => $customer->leases,
                "arp" => $customer->arp,
                "pppoe" => $customer->pppoe,
            ]
        ]);
    }
}