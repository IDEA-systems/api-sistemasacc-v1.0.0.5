<?php

require 'controller/database/retry/Retry.php';

Flight::route("GET /@usuario_id/retry/mikrotik", function($usuario_id) {
    $retry = new Retry();
    $retry->mikrotik_check_connected();
    Flight::json([]);
});

Flight::route("GET /@usuario_id/retry/whatsapp", function($usuario_id) {
    $retry = new Retry();
    $retry->retry_send_whatsapp();
    Flight::json([]);
});