<?php

$payment = new ReadPayment($cliente_id);
$rows = $payment->payment_customer($cliente_id);

if (empty($rows)) {
    Flight::json([
        "status" => 204,
        "title" => "No encontrado!",
        "details" => "No se encontraron filas!",
        "data" => $rows
    ]);
} 

if (!empty($rows)) {
    Flight::json([
        "status" => 301,
        "title" => "Encontrado!",
        "details" => "Mas de una fila fueron encontradas!",
        "data" => $rows
    ]);
}