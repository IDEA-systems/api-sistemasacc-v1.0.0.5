<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $payment = new ReadPayment();
    $rows = $payment->get_payment_by_type($type);
    Flight::json($rows);
}