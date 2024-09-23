<?php

require 'controller/database/equipment/Equipment.php';

Flight::route(
    "POST /@usuario_id/equipment", 
    function ($usuario_id) {
        require 'controller/database/equipment/create.php';
    }
);

Flight::route("GET /@usuario_id/equipment", function($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(412, "No autorizado!");
    }
    if ($is_user) {
        $equipment = new Equipment();
        Flight::json($equipment->get_all_equipment());
    }
});