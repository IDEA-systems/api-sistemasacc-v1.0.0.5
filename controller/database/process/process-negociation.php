<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $process = new Process();
    $process->process_negociation();
    
    if ($process->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error interno!",
            "details" => $process->error_message,
            "negociation" => $process->negociation,
        ]);
    }

    if (!$process->error) {
        Flight::json([
            "status" => 200,
            "title" => "Negociaciones!",
            "details" => "End process negociation!",
            "negociation" => $process->negociation,
        ]);
    }
}