<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user || !$is_root) {
    Flight::json([
        "status" => 401,
        "title" => "Sin autorización!",
        "details" => "No tiene permisos para realizar esta acción!"
    ]);
} 

if ($is_user && $is_root) {
    $request = Flight::request()->data;
    $payment = new Payment($request);
    $payment->authorize_payment();

    if ($payment->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error interno!",
            "details" => $payment->error_message
        ]);
    }

    if (!$payment->error) {
        Flight::json([
            "status" => 200,
            "title" => "Correcto!",
            "details" => "El pago fue autorizado correctamente!",
            "payment" => $payment->get_payment_by_id(),
            "activation" => $payment->activation,
            "morosos" => $payment->morosos
        ]);
    }
}