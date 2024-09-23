<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : '';
    $customers = new ReadCustomers();
    $rows = $customers->search_email(
        $cliente_email,
        $cliente_id
    );
    Flight::json($rows);
}