<?php

require 'controller/database/schedules/Schedules.php';

Flight::route("GET /@usuario_id/schedules", function ($usuario_id) {
    require 'controller/database/schedules/list-schedules.php';
});