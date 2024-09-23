<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $payment = new ReadPayment();
    $payment->count_totals_payments();
    Flight::json($payment);
}