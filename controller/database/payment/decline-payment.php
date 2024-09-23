<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user || !$is_root) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user && $is_root) {
    $request = Flight::request()->data;
    $payment = new Payment($request);
    $decline = $payment->decline_payment();

    if (!$decline) {
        Flight::json([
            "status" => 500,
            "title" => "Ocurrió un error!",
            "details" => "El proceso no se completo!",
            "mikrotik" => $payment->mikrotik,
            "data" => $decline
        ]);
    }

    if ($decline) {
        Flight::json([
            "status" => 200,
            "title" => "Proceso terminado!",
            "details" => "El pago fue rechazado correctamente!",
            "payment" => $payment->get_payment_by_id(),
            "mikrotik" => $payment->mikrotik,
            "morosos" => $payment->morosos
        ]);
    }
}