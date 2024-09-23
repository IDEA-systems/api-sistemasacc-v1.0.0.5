<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $payment = new ReadPayment();
    $rows = $payment->search_period($periodo_id, $cliente_id);
    Flight::json($rows);
}