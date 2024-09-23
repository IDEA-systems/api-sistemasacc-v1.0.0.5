<?php

require 'controller/database/status/Status.php';

Flight::route("GET /@usuario_id/status/clients", function($usuario_id) {
    require 'controller/database/status/status-clients.php';
});

Flight::route("GET /@usuario_id/status/installations", function($usuario_id) {
    require 'controller/database/status/status-installations.php';
});

Flight::route("GET /@usuario_id/status/equipos", function($usuario_id) {
    require 'controller/database/status/status-equipos.php';
});