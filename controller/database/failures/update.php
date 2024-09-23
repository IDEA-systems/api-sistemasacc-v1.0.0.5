<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$permisions = $login->get_permission($usuario_id);
$request = Flight::request()->data;

$empleado_control = isset($request->empleado_control) && 
    !is_null($request->empleado_control) && 
    !empty($request->empleado_control);

$tecnico = isset($request->tecnico) && 
    !is_null($request->tecnico) && 
    !empty($request->tecnico);

$prioridad = isset($request->prioridad) && 
    !is_null($request->prioridad) && 
    !empty($request->prioridad);

$fail_list = isset($request->fail_list) &&
    !is_null($request->fail_list) &&
    !empty($request->fail_list);

$reporte_descripcion = isset($request->reporte_descripcion) &&
    !is_null($request->reporte_descripcion) &&
    !empty($request->reporte_descripcion);

$reporte_domicilio = isset($request->reporte_domicilio) && 
    !is_null($request->reporte_domicilio) && 
    !empty($request->reporte_domicilio);

if (!$is_user || $permisions[0]["reports"] == 0) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user && $permisions[0]["reports"] == 1) {
    if (!$empleado_control) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "El encargado del reporte debe ser enviado!"
        ]);
    }
    else if (!$tecnico) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "El tecnico debe ser enviado!"
        ]);
    }
    else if (!$prioridad) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "La prioridad debe ser enviado!"
        ]);
    }
    else if (!$fail_list && !$reporte_descripcion) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "La descripción del reporte debe ser enviado!"
        ]);
    }
    else if (!$reporte_domicilio) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "Envie el domicilio del cliente!"
        ]);
    } 
    else {
        $failures = new Failures($request);

        $failures->update();

        if ($failures->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $failures->error_message
            ]);
        }

        if (!$failures->error) {
            Flight::json([
                "status" => 200,
                "title" => "Actualizado correctamente!",
                "details" => "El reporte fue actualizado correctamente!",
                "failure" => $failures->get_failure_by_id()
            ]);
        }
    }
}