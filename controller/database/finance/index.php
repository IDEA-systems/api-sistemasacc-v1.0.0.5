<?php

require 'controller/database/finance/Read.php';
// require 'controller/database/finance/Boxes.php';

Flight::route("GET /finance/dashboard", function () {
    $boxes = new ReadFinance();
    Flight::json([
        "status" => 200,
        "title" => "Datos encontrados!",
        "details" => "Mas de una fila encontrada!",
        "data" => $boxes
    ]);
});