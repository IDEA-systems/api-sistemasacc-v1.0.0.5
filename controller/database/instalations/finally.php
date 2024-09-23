<?php

$request = Flight::request()->data;

$user = new Login();
$is_user = $user->is_user($usuario_id);
$is_root = $user->is_root($usuario_id);

if (!$is_user && !$is_root) {
    Flight::json([
        "status" => 401,
        "title" => "No autorizado!",
        "details" => "El usuario no cuenta con permisos!"
    ]);
}

if ($is_user && $is_root) {

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

    $colonia = isset($request->colonia) &&
        !empty($request->colonia);

    $tecnico_asignado = isset($request->tecnico_asignado) &&
        !empty($request->tecnico_asignado);

    $fecha_agenda = isset($request->fecha_agenda) &&
        !empty($request->fecha_agenda);

    $tipo_pago = isset($request->tipo_pago) &&
        !empty($request->tipo_pago);

    $fecha_realizacion = isset($request->fecha_realizacion) &&
        !empty($request->fecha_realizacion);

    $servicios_instalados = isset($request->servicios_instalados) &&
        !empty($request->servicios_instalados);

    $receptor_pago = isset($request->receptor_pago) &&
        !empty($request->receptor_pago);

    $ubicacion_spliter = isset($request->ubicacion_spliter) &&
        !empty($request->ubicacion_spliter);

    $puerto_spliter = isset($request->puerto_spliter) &&
        !empty($request->puerto_spliter);

    $potencia = isset($request->potencia) &&
        !empty($request->potencia);

    $cable = isset($request->cable) &&
        !empty($request->cable);

    if (
        !$cliente_nombres || 
        !$cliente_apellidos || 
        !$cliente_telefono || 
        !$domicilio || 
        !$ubicacion || 
        !$colonia || 
        !$tecnico_asignado || 
        !$fecha_agenda ||
        !$tipo_pago ||
        !$fecha_realizacion ||
        !$servicios_instalados ||
        !$receptor_pago ||
        !$ubicacion_spliter ||
        !$puerto_spliter ||
        !$potencia ||
        !$cable
    ) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "Algunos datos faltan por enviarse!",
            "data" => $request
        ]);
    }

    if (
        $cliente_nombres  && 
        $cliente_apellidos  && 
        $cliente_telefono && 
        $domicilio && 
        $ubicacion && 
        $colonia && 
        $tecnico_asignado && 
        $fecha_agenda &&
        $tipo_pago &&
        $fecha_realizacion &&
        $servicios_instalados &&
        $receptor_pago &&
        $ubicacion_spliter &&
        $puerto_spliter &&
        $potencia &&
        $cable
    ) {
        $instalation = new Instalations($request);
        $instalation->status = 3;

        if ($instalation->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $instalation->error_message
            ]);
        }

        if (!$instalation->error) {
            // Despues guardar la instalacion
            $instalation->edit_instalation();

            if ($instalation->error) {
                Flight::json([
                    "status" => 500,
                    "title" => "Error interno!",
                    "details" => $instalation->error_message
                ]);
            }

            if (!$instalation->error) {
                Flight::json([
                    "status" => 200,
                    "title" => "Finalizada correctamente!",
                    "details" => "La instalación fue finalizada correctamente!",
                    "installation" => $instalation->get_instalation_byid($request->instalacion_id),
                    "dhcp" => $instalation->dhcp,
                    "arp" => $instalation->arp,
                    "pppoe" => $instalation->pppoe
                ]);
            }
        }
    }

}