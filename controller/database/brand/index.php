<?php

require 'controller/database/brand/Read.php';
require 'controller/database/brand/Brand.php';

Flight::route("GET /@usuario_id/system/config", function ($usuario_id) {
    require 'controller/database/brand/configuration.php';
});

Flight::route("GET /@usuario_id/system/messages/", function ($usuario_id) {
    require 'controller/database/brand/templates.php';
});

Flight::route("POST /@usuario_id/system/enterprice/", function ($usuario_id) {
    require 'controller/database/brand/enterprice.php';
});

Flight::route("GET /@usuario_id/system/enterprice/", function ($usuario_id) {
    require 'controller/database/brand/brand-info.php';
});

Flight::route("POST /@usuario_id/system/messages/", function ($usuario_id) {
    require 'controller/database/brand/messages.php';
});

Flight::route("GET /@usuario_id/system/config/enable/@id", function ($usuario_id, $id) {
    require 'controller/database/brand/enable.php';
});


Flight::route("GET /@usuario_id/system/config/disable/@id", function ($usuario_id, $id) {
    require 'controller/database/brand/disabled.php';
});
