<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $process = new Process();
    $process->add_morosos();
    
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
            "title" => "Mikrotik suspension!",
            "details" => "El proceso de suspension de morosos se ha ejecutado correctamente!",
            "negociation" => $process->morosos,
        ]);
    }
}