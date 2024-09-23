<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Usuario no autorizado!");
} 

if ($is_user) {
    $failures = new Failures();
    $failures->change_status($reporte_id);

    if ($failures->error) {
        Flight::halt(500, $failures->error_message);
    }

    if (!$failures->error) {
        Flight::json([
            "status" => 200,
            "title" => "Reporte en atención!",
            "details" => "El reporte ahora será atendido por un técnico!",
            "failure" => $failures->get_failure_by_id(),
            "whatsapp" => $failures->whatsapp
        ]);
    }
}
