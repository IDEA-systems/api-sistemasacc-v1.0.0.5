<?php

// Obtener las interfaces de un mikrotik
Flight::route(
    'GET /@usuario_id/routeros/interfaces/@mikrotik_id', 
    function ($usuario_id, $mikrotik_id) {
        require 'controller/routeros/interfaces.php';
    }
);

Flight::route(
    'GET /@usuario_id/routeros/monitor/@mikrotik_id/@interface', 
    function ($usuario_id, $mikrotik_id, $interface) {
        require 'controller/routeros/monitor.php';
    }
);

Flight::route('POST /routeros/conectar', function () {
    require 'controller/routeros/connection.php';
});

Flight::route('POST /routeros/monitor/@interface', function ($interface) {
    include_once ('controller/routeros/monitor.php');
});

Flight::route("GET /@usuario_id/routeros/backup", function ($usuario_id) {
    require 'controller/routeros/backup.php';
});


Flight::route('POST /@usuario_id/routeros/mostrar', function ($usuario_id) {
    require 'controller/routeros/getall.php';
});

Flight::route('POST /@usuario_id/routeros/mostrar/@search_data', function ($usuario_id, $search_data) {
    require 'controller/routeros/search.php';
});

Flight::route('POST /@usuario_id/routeros/agregar', function ($usuario_id) {
    require 'controller/routeros/add.php';
});

Flight::route('GET /routeros/suspended', function () {
    include_once ('controller/routeros/suspended.php');
});

Flight::route('POST /routeros/ping/@ip', function ($ip) {
    include_once ('controller/routeros/ping.php');
});

Flight::route('POST /routeros/editar', function () {
    include_once ('controller/routeros/update.php');
});

Flight::route('POST /routeros/remove', function () {
    include_once ('controller/routeros/remove.php');
});


?>