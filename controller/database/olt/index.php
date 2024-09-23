<?php
require 'controller/database/olt/Olt.php';

Flight::route("POST /@usuario_id/olt", function ($usuario_id) {
    require 'controller/database/olt/create.php';
});

Flight::route("GET /@usuario_id/olt", function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(412, "No autorizado!");
    }
    if ($is_user) {
        $olt = new Olt();
        Flight::json($olt->get_all_olt());
    }
});