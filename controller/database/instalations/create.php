<?php

$request = Flight::request()->data;

$user = new Login();
$is_user = $user->is_user($usuario_id);

if (!$is_user) {
    Flight::json([
        "status" => 401,
        "title" => "No autorizado!",
        "details" => "El usuario no cuenta con permisos!"
    ]);
}

if ($is_user) {

    $cliente_nombres = isset($request->cliente_nombres) &&
        !empty($request->cliente_nombres);

    $cliente_apellidos = isset($request->cliente_apellidos) &&
        !empty($request->cliente_apellidos);
    
    $cliente_telefono = isset($request->cliente_telefono) &&
        !empty($request->cliente_telefono);

    $domicilio = isset($request->domicilio) &&
        !empty($request->domicilio);

    $ubicacion = isset($request->ubicacion) &&
        !empty($request->ubicacion);

    $tecnico_asignado = isset($request->tecnico_asignado) &&
        !empty($request->tecnico_asignado);

    $fecha_agenda = isset($request->fecha_agenda) &&
        !empty($request->fecha_agenda);

    if (
        !$cliente_nombres || 
        !$cliente_apellidos || 
        !$cliente_telefono || 
        !$domicilio || 
        !$ubicacion ||
        !$fecha_agenda
    ) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "Nombres, Apellidos, Telefono, Domicilio, Tecnico, Colonia, Promotor y Fecha son requeridos!",
            "data" => $request
        ]);
    }

    if (
        $cliente_nombres  && 
        $cliente_apellidos  && 
        $cliente_telefono && 
        $domicilio && 
        $ubicacion &&
        $fecha_agenda
    ) {
        $instalation = new Instalations($request);

        if ($instalation->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $instalation->error_message
            ]);
        }

        if (!$instalation->error) {
            // Despues guardar la instalacion
            $instalation->create_instalation();

            if ($instalation->error) {
                Flight::json([
                    "status" => 500,
                    "title" => "Incorrecto!",
                    "details" => $instalation->error_message
                ]);
            }

            if (!$instalation->error) {
                Flight::json([
                    "status" => 200,
                    "title" => "Actualizado correctamente!",
                    "details" => "La instalación fue actualizado correctamente!",
                    "whatsapp" => $instalation->whatsapp,
                    "installation" => $instalation->get_instalation_byid($instalation->instalacion_id),
                    "dhcp" => $instalation->dhcp,
                    "arp" => $instalation->arp,
                    "pppoe" => $instalation->pppoe
                ]);
            }
        }
    }

}