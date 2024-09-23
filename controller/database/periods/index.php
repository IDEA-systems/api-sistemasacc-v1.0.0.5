<?php

require 'controller/database/periods/Read.php';

Flight::route("GET /@usuario_id/periods", function($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado");
    }

    if ($is_user) {
        $periods = new ReadPeriods();
        $periods = $periods->get_all_periods();
        Flight::json($periods);
    }
});