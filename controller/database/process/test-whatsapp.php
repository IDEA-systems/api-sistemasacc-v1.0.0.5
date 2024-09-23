<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    $process = new Process(); //9331002094
    $process->testWhatsapp($usuario_id);

    if (!$process->whatsapp) {
        Flight::json([
            "status" => 500,
            "title" => "Whatsapp error",
            "details" => "Servicio de whatsapp desconectado!",
            "whatsapp" => $process->whatsapp,
        ]);
    }
    
    if ($process->whatsapp) {
        Flight::json([
            "status" => 200,
            "title" => "Whatsapp success",
            "details" => "La API de whatsapp esta conectada!",
            "whatsapp" => $process->whatsapp,
        ]);
    }
}