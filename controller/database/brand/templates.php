<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Sin autorizacion!");
} 

if ($is_user) {
    $config = new Brand();
    $data = $config->get_messages();
    Flight::json($data);
}