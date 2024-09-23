<?php
require 'controller/database/services/Services.php';

Flight::route(
    "POST /@usuario_id/servicios", 
    function($usuario_id) {
        require 'controller/database/services/create.php';
    }
);

Flight::route(
    "POST /@usuario_id/servicios/update", 
    function($usuario_id) {
        require 'controller/database/services/update.php';
    }
);

Flight::route(
    "DELETE /@usuario_id/servicios/disable/@servicio_id", 
    function($usuario_id, $servicio_id) {
        require 'controller/database/services/disable.php';
    }
);

Flight::route(
    "GET /@usuario_id/servicios/enable/@servicio_id", 
    function($usuario_id, $servicio_id) {
        require 'controller/database/services/enable.php';
    }
);


Flight::route("GET /@usuario_id/servicios", function($usuario_id) {
    require 'controller/database/services/list.php';
});