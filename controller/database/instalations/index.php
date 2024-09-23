<?php

require 'controller/database/instalations/Installations.php';

Flight::route("POST /@usuario_id/installations", function($usuario_id) {
    require 'controller/database/instalations/create.php';
});


Flight::route("GET /@usuario_id/installations", function($usuario_id) {
    require 'controller/database/instalations/list.php';
});

Flight::route("POST /@usuario_id/installations/editar", function($usuario_id) {
    require 'controller/database/instalations/update.php';
});

Flight::route("POST /@usuario_id/installations/finalizar", function($usuario_id) {
    require 'controller/database/instalations/finally.php';
});

Flight::route("GET /@usuario_id/installations/instalar/@instalacion_id", function($usuario_id, $instalacion_id) {
    require 'controller/database/instalations/instalar.php';
});

Flight::route("DELETE /@usuario_id/installations/@instalacion_id", function($usuario_id, $instalacion_id) {
    require 'controller/database/instalations/remove.php';
});