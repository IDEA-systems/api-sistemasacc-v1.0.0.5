<?php

require 'controller/database/process/Process.php';

Flight::route("/@usuario_id/process/whatsapp", function ($usuario_id) {
    require 'controller/database/process/test-whatsapp.php';
});

Flight::route("/@usuario_id/process/suspension", function ($usuario_id) {
    require 'controller/database/process/layoff-database.php';
});

Flight::route("/@usuario_id/process/suspension/mikrotik", function ($usuario_id) {
    require 'controller/database/process/layoff-mikrotik.php';
});

Flight::route("/@usuario_id/process/freemonth", function ($usuario_id) {
    require 'controller/database/process/free-month.php';
});

Flight::route("/@usuario_id/process/negociation", function ($usuario_id) {
    require 'controller/database/process/finish-negociation.php';
});

Flight::route("/@usuario_id/process/failures", function ($usuario_id) {
    require 'controller/database/process/failures.php';
});

Flight::route("/@usuario_id/process/backup", function ($usuario_id) {
    require 'controller/database/process/backups.php';
});

Flight::route("/@usuario_id/process/messenger", function ($usuario_id) {
    require 'controller/database/process/messenger.php';
});