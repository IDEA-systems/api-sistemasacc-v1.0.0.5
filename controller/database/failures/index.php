<?php

require 'controller/database/failures/Failures.php';

/**
 *  0 RECHAZADO, 
 *  1 REVISION, 
 *  2 FINALIZADO 
 * 
*/

/* Add new report */
Flight::route("POST /@usuario_id/failures", function ($usuario_id) {
    require 'controller/database/failures/create.php';
});

Flight::route("POST /@usuario_id/failures/@reporte_id", function ($usuario_id, $reporte_id) {
    require 'controller/database/failures/update.php';
});

Flight::route("POST /@usuario_id/failures/@reporte_id/finish", function ($usuario_id, $reporte_id) {
    require 'controller/database/failures/finish.php';
});

Flight::route("GET /@usuario_id/failures/common/fails", function($usuario_id) {
    require 'controller/database/failures/common-failures.php';
});

Flight::route("GET /@usuario_id/failures", function ($usuario_id) {
    require 'controller/database/failures/get-failures.php';
});

Flight::route("GET /@usuario_id/priorities", function ($usuario_id) {
    require 'controller/database/failures/get-priorities.php';
});

Flight::route("GET /@usuario_id/failures/details/@reporte_id", function ($usuario_id, $reporte_id) {
    require 'controller/database/failures/details-failure.php';
});

Flight::route("GET /@usuario_id/failures/list/status", function ($usuario_id) {
    require 'controller/database/failures/get-status.php';
});

Flight::route("GET /@usuario_id/failures/mark/revision/@reporte_id", function ($usuario_id, $reporte_id) {
    require 'controller/database/failures/change-status.php';
});
