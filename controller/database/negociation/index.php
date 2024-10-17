<?php

/**
 * Negociaciones
 * 0 Rechazada
 * 1 Corriendo
 * 2 Revision
 * 3 Finalizada
 */

require 'controller/database/negociation/Negociation.php';
require 'controller/database/negociation/Read.php';

Flight::route("POST /@usuario_id/negociation", function ($usuario_id) {
    require 'controller/database/negociation/create.php';
});


Flight::route("GET /@usuario_id/negociation", function ($usuario_id) {
    require 'controller/database/negociation/getall.php';
});

Flight::route("GET /@usuario_id/negociation/@cliente_id", function ($usuario_id, $cliente_id) {
    require 'controller/database/negociation/client-negociation.php';
});