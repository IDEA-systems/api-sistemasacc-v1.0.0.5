<?php

  $body = Flight::request()->getBody();
  $request = json_decode($body, true);

  if (
    !isset($request['destinatario']) ||
    !isset($request['importancia']) ||
    !isset($request['fecha_caducidad'])
  ) {
    Flight::json(array(
      "status" => 400,
      "title" => "¡Bad request!",
      "details" => "¡No se enviaron datos requeridos!",
      "data" => $request['destinatario']
    ));
  }
    
  if (
    isset($request['destinatario']) &&
    isset($request['importancia']) &&
    isset($request['fecha_caducidad'])
  ) {
    
    $fecha = date('Y-m-d');
    $titulo = $request['titulo'];
    $aviso_mensaje = $request['aviso_mensaje'];
    $destinatario = $request['destinatario'];
    $importancia = $request['importancia'];
    $status_aviso = $request['status_aviso'];
    $fecha_caducidad = $request['fecha_caducidad'];
    $visto = $request['visto'];

    $SQL = " INSERT INTO `avisos` VALUES (NULL,'$fecha','$titulo','$aviso_mensaje','$destinatario','$importancia','$status_aviso','$fecha_caducidad','$visto') ";
    $query = Flight::gnconn()->prepare($SQL);
    $query->execute();


    $SQL = "SELECT * FROM avisos WHERE destinatario = '$destinatario' AND importancia = '$importancia' AND status_aviso = $status_aviso AND fecha = '$fecha' AND fecha_caducidad = '$fecha_caducidad'";
    $query = Flight::gnconn()->prepare($SQL);
    $query->execute();
    $rows = $query->fetchAll();

    if (count($rows) == 0) {
  
      Flight::json(array(
        "status" => 500,
        "title" => "¡No agregado!",
        "details" => "¡No agregamos los datos!",
        "data" => []
      ));
  
    } else {
  
      Flight::json(array(
        "status" => 200,
        "title" => "¡Datos encontrados!",
        "details" => "¡Más de una fila encontrada!",
        "data" => $rows
      ));
  
    }    
  }

    

    



?>