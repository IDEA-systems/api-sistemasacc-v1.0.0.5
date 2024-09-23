<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Usuario no autorizado!");
} 

if ($is_user) {
    $failures = new Failures();
    $rows = $failures->get_status_failures();
    Flight::json($rows);
}
