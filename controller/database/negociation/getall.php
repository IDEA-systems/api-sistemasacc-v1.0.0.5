<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
  $limit = isset($_GET["limit"]) ? "LIMIT ".$_GET["limit"] : "";
  $status = isset($_GET["status"]) ? $_GET["status"] : "IN(1,2,3)";
  $empleado_id = isset($_GET["empleado_id"]) ? $_GET["empleado_id"] : null;
  $fecha_inicio = isset($_GET["fecha_inicio"]) ? $_GET["fecha_inicio"] : null;
  $fecha_fin = isset($_GET["fecha_fin"]) ? $_GET["fecha_fin"] : null;
  $parameters = isset($_GET["parameters"]) ? $_GET["parameters"] : null;

  $negociation = new Negociation();
  
  $list = $negociation->get_all_negociations([
    "limit" => $limit,
    "status" => $status,
    "empleado_id" => $empleado_id,
    "fecha_inicio" => $fecha_inicio,
    "fecha_fin" => $fecha_fin,
    "parameters" => $parameters,
  ]);

  if ($negociation->error) {
    Flight::json([
      "status" => 500,
      "title" => "Error interno!",
      "details" => $negociation->error_message
    ]);
  }
  
  if (!$negociation->error) {
    Flight::json($list);
  }
}