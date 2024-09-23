<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ($is_user) {
    $cologne = new Cologne();
    $list = $cologne->get_all_colognes();
    Flight::json($list);
}