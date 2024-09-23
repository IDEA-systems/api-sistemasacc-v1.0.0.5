<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::json([
        "status" => 401,
        "title" => "No autorizado",
        "details" => "El usuario no tiene permisos!"
    ]);
}

if ($is_user) {

    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : null;
    $promotor_id = isset($_GET['promotor_id']) ? $_GET['promotor_id'] : null;
    $tecnico_asignado = isset($_GET['tecnico_asignado']) ? $_GET['tecnico_asignado'] : null;
    $empleado_captura = isset($_GET['empleado_captura']) ? $_GET['empleado_captura'] : null;
    $empleado_finalizacion = isset($_GET['empleado_finalizacion']) ? $_GET['empleado_finalizacion'] : null;
    $empleado_agenda = isset($_GET['empleado_agenda']) ? $_GET['empleado_agenda'] : null;
    $colonia_id = isset($_GET['colonia_id']) ? $_GET['colonia_id'] : null;
    $receptor_pago = isset($_GET['receptor_pago']) ? $_GET['receptor_pago'] : null;
    $fecha_recepcion = isset($_GET['fecha_recepcion']) ? $_GET['fecha_recepcion'] : null;
    $fecha_agenda = isset($_GET['fecha_agenda']) ? $_GET['fecha_agenda'] : null;
    $horario_id = isset($_GET['horario_id']) ? $_GET['horario_id'] : null;
    $fecha_realizacion = isset($_GET['fecha_realizacion']) ? $_GET['fecha_realizacion'] : null;
    $fecha_finalizacion = isset($_GET['fecha_finalizacion']) ? $_GET['fecha_finalizacion'] : null;
    $pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

    $filters = array("search" => $search, "status" => $status, "cliente_id" => $cliente_id, "promotor_id" => $promotor_id, "tecnico_asignado" => $tecnico_asignado, "empleado_captura" => $empleado_captura, "empleado_finalizacion" => $empleado_finalizacion, "empleado_agenda" => $empleado_agenda, "colonia_id" => $colonia_id, "receptor_pago" => $receptor_pago, "fecha_recepcion" => $fecha_recepcion, "fecha_agenda" => $fecha_agenda, "horario_id" => $horario_id, "fecha_realizacion" => $fecha_realizacion, "fecha_finalizacion" => $fecha_finalizacion, "pagina" => $pagina);
    
    $installation = new Instalations();
    $rows = $installation->get_all_instalations($filters);
    $total = $installation->get_total_installations();
    
    Flight::json([
        "list" => $rows,
        "total" => $total
    ]);
}