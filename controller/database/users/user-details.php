<?php

$login = new Login($usuario_id);
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ($is_user) {
    $user = new ReadUser();
    $details = $user->get_user_by_id($usuario_id);
    Flight::json($details);
}