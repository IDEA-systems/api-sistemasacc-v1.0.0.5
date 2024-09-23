<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $payment = new ReadPayment();
    $rows = $payment->search_payment_params($search_params);
    
    if (empty($rows)) {
        Flight::json([
            "status" => 204,
            "title" => "No encontrado!",
            "details" => "No se encontraron resultados",
            "data" => []
        ]);
    }

    if (!empty($rows)) {
        Flight::json([
            "status" => 302,
            "title" => "Encontrado!",
            "details" => "Resultados encontrados para: $search_params",
            "data" => $rows
        ]);
    }
}