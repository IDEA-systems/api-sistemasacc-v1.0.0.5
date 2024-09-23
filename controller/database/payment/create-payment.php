<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::json([
        "status" => 401,
        "title" => "Sin autorización!",
        "details" => "No tiene permisos para esta accion",
        "data" => $request,
    ]);
} 

if ($is_user) {
    $request = Flight::request()->data;
    $files = Flight::request()->files;
    $payment = new Payment($request, $files);
    $payment->save_payment();

    if ($payment->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error!",
            "details" => $payment->error_message
        ]);
    }

    if (!$payment->error) {
        Flight::json([
            "status" => 200,
            "title" => "Agregado!",
            "details" => "Pago agregado correctamente!",
            "customer" => $payment->get_customer_by_id(),
            "comprobante" => $payment->save_payment_file($files),
            "periodos" => implode("-", $request->periodo_id),
            "activation" => $payment->activation,
            "morosos" => $payment->morosos,
            "whatsapp" => $payment->whatsapp
        ]);
    }
}