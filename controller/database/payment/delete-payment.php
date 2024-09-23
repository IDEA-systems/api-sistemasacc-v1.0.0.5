<?php

$login = new Login();
$permision = $login->get_permission($usuario_id);

if ($permision[0]["destroy"] == 0) {
    Flight::halt(401, "El usuario no esta autorizado!");
} 

if ($permision[0]["destroy"] == 1) {
    $pago_id = isset($_GET['pago_id']) ? $_GET['pago_id'] : null;
    $cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : null;

    $payment = new Payment();
    $payment->cliente_id = $cliente_id;
    $payment->pago_id = $pago_id;

    $payment->delete_from_database();

    if ($payment->error) {
        Flight::json([
            "status" => 500,
            "title" => "Ocurrió un error!",
            "details" => $payment->error_message
        ]);
    }

    if (!$payment->error) {
        Flight::json([
            "status" => 200,
            "title" => "Proceso terminado!",
            "details" => "El pago fue procesado correctamente!",
            "activacion" => $payment->activation,
            "morosos" => $payment->morosos
        ]);
    }
}