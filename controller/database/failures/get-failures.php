<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "Usuario no autorizado!");
} 

if ($is_user) {
    $failures = new Failures();
    $status = isset($_GET['status']) ? intval($_GET['status']) : null;
    $prioridad = isset($_GET['prioridad']) ? intval($_GET['prioridad']) : null;
    $fecha_inicio = isset($_GET['fecha_inicio']) ? intval($_GET['fecha_inicio']) : null;
    $fecha_fin = isset($_GET['fecha_fin']) ? intval($_GET['fecha_fin']) : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : null;
    $filters = [ "status" => $status, "prioridad" => $prioridad, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin, "search" => $search, "cliente_id" => $cliente_id ];

    $rows = $failures->get_failures($filters);
    
    Flight::json([
        "status" => 301,
        "title" => "Encontrado!",
        "details" => "Los datos del cliente fueron encontados!",
        "data" => $rows
    ]);
}
