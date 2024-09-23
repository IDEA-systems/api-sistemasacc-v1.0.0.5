<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $request = Flight::request()->data;
    $customers = new ReadCustomers();
    $customers->get_consunption_customer($request);
    Flight::json($customers->consumption);
}