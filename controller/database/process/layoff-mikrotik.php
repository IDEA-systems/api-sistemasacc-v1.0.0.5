<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $process = new Process();
    $process->layoff_in_mikrotik();
    
    if (!$process->error) {
        Flight::json([
            "status" => 500,
            "title" => "Error interno!",
            "details" => $process->error_message,
            "morosos" => $process->morosos,
        ]);
    }

    if ($process->error) {
        Flight::json([
            "status" => 200,
            "title" => "Morosos!",
            "details" => "Process complete!",
            "morosos" => $process->morosos,
        ]);
    }
}