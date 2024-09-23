<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado");
}

if ($is_user) {
    $schedules = new Schedules();
    $installations = $schedules->get_schedules_installation();
    Flight::json($installations);
}