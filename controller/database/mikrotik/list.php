<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $mikrotik = new Mikrotiks();
    $mikrotiks = $mikrotik->get_all_mikrotiks();
    Flight::json($mikrotiks);
}