<?php

require 'controller/database/modems/Modem.php';
require 'controller/database/modems/Read.php';

Flight::route("POST /@usuario_id/modems", function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    } 
    
    if ($is_user) {
        $request = Flight::request()->data;
        $modems = new Modems($request);
        $create = $modems->Create();

        if (!$create) {
            Flight::json([ 
                "status" => 500,
                "title" => "Conflicto!",
                "details" => $modems->error_message
            ]);
        }

        if ($create) {
            $idmodem = $modems->idmodem;
            $data = $modems->get_modem_by_id($idmodem);
            Flight::json([
                "status" => 200,
                "title" => "Creado!",
                "details" => "Modem registrado correctamente",
                "data" => $data,
                "modem" => $idmodem
            ]);
        }
    }
});

Flight::route("GET /@usuario_id/modems", function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado");
    }

    if ($is_user) {
        $modem = new ReadModems();
        $modem = $modem->get_all_modems();
        Flight::json($modem);
    }
});