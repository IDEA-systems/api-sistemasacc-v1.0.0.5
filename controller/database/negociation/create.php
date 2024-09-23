<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $request = Flight::request()->data;
    $negociation = new Negociation($request);
    $negociation->save_negociation($is_root);

    if ($negociation->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error!",
            "details" => $negociation->error_message
        ]);
    }
    
    if (!$negociation->error) {
        Flight::json([
            "status" => 200,
            "title" => "Agregado!",
            "details" => "Negociacion agregada correctamente!",
            "data" => $negociation->negociacion,
            "customer" => $negociation->get_customer_by_id(),
            "activacion" => $negociation->activacion,
            "whatsapp" => $negociation->whatsapp
        ]);
    }
}