<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $files = Flight::request()->files;
    $request = Flight::request()->data;
    $customers = new Customer($request, $files);
    $customers->update_image();
    $customers->save_file_customer_ine($files);

    if ($customers->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error interno!",
            "details" => $customers->error_message
        ]);
    }

    if (!$customers->error) {
        Flight::json([
            "status" => 200,
            "title" => "Correcto!",
            "details" => "Ine actualizada correctamente!",
            "customer" => $customers->get_customer_by_id()
        ]);
    }
}