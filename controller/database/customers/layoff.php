<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $customers = new Customer();
    $customers->layoff_customer($cliente_id);

    if ($customers->error) {
        Flight::json([
            "status" => 304,
            "title" => "No modificado!",
            "details" => $customers->error_message,
            "data" => $cliente_id
        ]);
    }

    if (!$customers->error) {
        Flight::json([
            "status" => 200,
            "title" => "Suspendido!",
            "details" => "El cliente ha sido suspendido!",
            "data" => $customers->get_customer_by_id(),
            "morosos" => $customers->morosos,
            "whatsapp" => $customers->whatsapp
        ]);
    }
}