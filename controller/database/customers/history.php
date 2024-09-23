<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $customers = new ReadCustomers();
    $customers->history($cliente_id);
    Flight::json([
        "payment" => $customers->history_payment,
        "negociation" => $customers->history_negociation,
        "failures" => $customers->history_reports
    ]);
}