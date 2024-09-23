<?php

require 'controller/database/mikrotik/Read.php';
require 'controller/database/mikrotik/Mikrotik.php';

Flight::route('POST /@usuario_id/mikrotik', function ($usuario_id) {
    require 'controller/database/mikrotik/create.php';
});

Flight::route('POST /@usuario_id/mikrotik/update', function ($usuario_id) {
    require 'controller/database/mikrotik/update.php';
});

Flight::route("GET /@usuario_id/mikrotik", function($usuario_id) {
    require 'controller/database/mikrotik/actives.php';
});

Flight::route('GET /@usuario_id/mikrotik/all', function ($usuario_id) {
    require 'controller/database/mikrotik/list.php';
});

Flight::route('POST /@usuario_id/mikrotik/configuration', function ($usuario_id) {
    require 'controller/database/mikrotik/add-config.php';
});

Flight::route('POST /@usuario_id/mikrotik/password', function ($usuario_id) {
    require 'controller/database/mikrotik/change-password.php';
});

Flight::route('GET /mikrotik/administracion', function () {
    $mikrotiks = new ReadMikrotik();
    Flight::json([
        "status" => 200,
        "title" => "¡Datos encontrados!",
        "details" => "¡Más de una fila encontrada!",
        "data" => $mikrotiks->method_admins
    ]);
});
