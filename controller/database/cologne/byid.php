<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ($is_user) {
    $cologne = new Cologne();
    $cologne->colonia_id = $colonia_id;
    $list = $cologne->get_cologne_by_id();
    Flight::json($list);
}