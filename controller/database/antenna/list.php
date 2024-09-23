<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ($is_user) {
    $antenna = new Antenna();
    $array = $antenna->get_all_antennas();
    Flight::json($array);
}