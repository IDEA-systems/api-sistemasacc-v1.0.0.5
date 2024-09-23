<?php

require 'controller/database/antenna/Antenna.php';
require 'controller/database/antenna/Read.php';

/** Add new antenna from database */
Flight::route("POST /@usuario_id/antenna", function ($usuario_id) {
    require 'controller/database/antenna/create.php';
});

// Update antena
Flight::route("POST /@usuario_id/antenna/update", function ($usuario_id) {
    require 'controller/database/antenna/update.php';
});

// Get all antennas
Flight::route("GET /@usuario_id/antenna", function ($usuario_id) {
    require 'controller/database/antenna/list.php';
});
