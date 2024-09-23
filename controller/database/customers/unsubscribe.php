<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);
$permission = $login->get_permission($usuario_id);
$unsubscribe = $permission[0]["unsubscribe"] == 1;

if (!$unsubscribe ||!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($unsubscribe && $is_user) {
    $customers = new Customer();
    $customers->unsubscribe($cliente_id, $is_root);

    if ($customers->error) {
        Flight::json([ 
            "status" => 409,
            "title" => "Conflicto!",
            "details" => $customers->error_message
        ]);
    }

    if (!$customers->error) {
        Flight::json([
            "status" => 200,
            "title" => "Bloqueado!",
            "details" => "Cliente bloqueado correctamente!",
            "data" => $customers->get_customer_by_id(),
            "morosos" => $customers->morosos
        ]);
    }
}