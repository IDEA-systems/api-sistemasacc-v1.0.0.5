<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $customers = new ReadCustomers();
    $cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : '';
    $rows = $customers->search_phone(
        $cliente_telefono, 
        $cliente_id
    );
    Flight::json($rows);
}