<?php

require 'controller/database/payment/Read.php';
require 'controller/database/payment/Payment.php';

Flight::route("POST /@usuario_id/payment", function ($usuario_id) {
    require 'controller/database/payment/create-payment.php';
});

Flight::route("POST /@usuario_id/payment/authorize", function ($usuario_id) {
    require 'controller/database/payment/authorize-payment.php';
});

Flight::route("POST /@usuario_id/payment/decline", function ($usuario_id) {
    require 'controller/database/payment/decline-payment.php';
});

Flight::route("DELETE /@usuario_id/payment", function ($usuario_id) {
    require 'controller/database/payment/delete-payment.php';
});

Flight::route("GET /@usuario_id/payment", function ($usuario_id) {
    require 'controller/database/payment/all-payment.php';
});


Flight::route("GET /@usuario_id/payment/count/all", function ($usuario_id) {
    require 'controller/database/payment/count-payment.php';
});

Flight::route("GET /@usuario_id/payment/type/@type", function ($usuario_id, $type) {
    require 'controller/database/payment/payment-by-type.php';
});