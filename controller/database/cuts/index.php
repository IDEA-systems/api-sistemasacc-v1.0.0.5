<?php

require 'controller/database/cuts/Read.php';
require 'controller/database/cuts/Cuts.php';

Flight::route(
    "POST /@usuario_id/cuts/update", 
    function($usuario_id) {
        require 'controller/database/cuts/update.php';
    }
);

Flight::route(
    "GET /@usuario_id/cuts", 
    function($usuario_id) {
        require 'controller/database/cuts/list.php';
    }
);