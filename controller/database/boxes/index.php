<?php
  
  require 'controller/database/boxes/Read.php';
  // require 'controller/database/boxes/Boxes.php';

  Flight::route("GET /boxes/dashboard", function() {
    $boxes = new ReadBoxes();
    Flight::json(
      array(
        "status" => 200,
        "title" => "Datos encontrados!",
        "details" => "Mas de una fila encontrada!",
        "data" => $boxes
      )
    );
  });

?>