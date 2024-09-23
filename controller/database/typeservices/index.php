<?php

require 'controller/database/typeservices/Read.php';

Flight::route("GET /@usuario_id/typeservices", function($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado");
    }

    if ($is_user) {
        $services = new ReadTypeServices();
        $services = $services->get_all_services();
        Flight::json($services);
    }
});