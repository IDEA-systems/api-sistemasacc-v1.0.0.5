<?php

$login = new Login($usuario_id);
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if ( !$is_user || !$is_root) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ($is_user && $is_root) {
    $user = new ReadUser();
    Flight::json($user->get_all_users());
}