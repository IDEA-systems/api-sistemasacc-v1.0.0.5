<?php
// Read methods class
require 'controller/database/methods/Read.php';

Flight::route("GET /@usuario_id/administracion", function($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
      Flight::halt(401, "No autorizado!");
    } else {
      $methods = new ReadMethods();
      Flight::json($methods->get_all_methods());
    }
});