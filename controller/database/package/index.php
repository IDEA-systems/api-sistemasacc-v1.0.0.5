<?php

require 'controller/database/package/Read.php';
require 'controller/database/package/Package.php';

Flight::route("GET /@usuario_id/packages", function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(404, "Bad request");
    }
    if ($is_user) {
        $packages = new ReadPackages();
        Flight::json($packages->get_all_packages());
    }
});

Flight::route("POST /@usuario_id/packages", function ($usuario_id) {
    require 'controller/database/package/create.php';
});

Flight::route("POST /@usuario_id/packages/update", function ($usuario_id) {
    require 'controller/database/package/update.php';
});

Flight::route("DELETE /@usuario_id/packages/@idpaquete", function ($usuario_id, $idpaquete) {
    require 'controller/database/package/delete.php';
});

?>