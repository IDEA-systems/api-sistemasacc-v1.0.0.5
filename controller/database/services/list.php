<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(412, "No autorizado!");
}

if ($is_user) {
    $servicios = new Services();
    Flight::json($servicios->get_all_services());
}