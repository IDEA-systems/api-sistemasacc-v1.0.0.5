<?php
require 'controller/database/addresses/Addresses.php';

Flight::route("GET /@usuario_id/addresses", function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(412, "No autorizado!");
    }
    if ($is_user) {
        $antennas = new Addresses();
        Flight::json($antennas->get_all_addresses());
    }
});