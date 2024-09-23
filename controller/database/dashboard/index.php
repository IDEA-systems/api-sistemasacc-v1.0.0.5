<?php

require 'controller/database/dashboard/Read.php';
// resources from pages dashboards //
Flight::route('GET /@usuario_id/dashboard', function () {
    $dashboard = new Dashboard();
    $data = $dashboard->home();
    Flight::json($data);
});

Flight::route('GET /@usuario_id/dashboard/failures', function () {
    $dashboard = new Dashboard();
    $data = $dashboard->failures();
    Flight::json($data);
});

Flight::route('GET /@usuario_id/dashboard/finance', function () {
    $dashboard = new Dashboard();
    $data = $dashboard->finance();
    Flight::json($data);
});