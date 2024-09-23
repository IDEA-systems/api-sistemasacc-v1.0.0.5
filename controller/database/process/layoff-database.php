<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $process = new Process();
    $process->layoff_customers();
    
    if ($process->error) {
        Flight::json([
            "status" => 500,
            "title" => "Suspension error",
            "details" => $process->error_message,
            "suspension" => $process->suspension,
        ]);
    }

    if (!$process->error) {
        Flight::json([
            "status" => 200,
            "title" => "Suspension",
            "details" => "End process suspension",
            "suspension" => $process->suspension,
        ]);
    }
}