<?php 
$request = Flight::request()->data;
$files = Flight::request()->files;

$cliente_nombres = !isset($request->cliente_nombres) 
    || is_null($request->cliente_nombres) 
    || $request->cliente_nombres == '';

$cliente_apellidos = !isset($request->cliente_apellidos) 
    || is_null($request->cliente_apellidos) 
    || $request->cliente_apellidos == '';

// $cliente_email = !isset($request->cliente_email) 
//     || is_null($request->cliente_email) 
//     || $request->cliente_email == '';

$cliente_telefono = !isset($request->cliente_telefono) 
    || is_null($request->cliente_telefono) 
    || $request->cliente_telefono == '';

$cliente_domicilio = !isset($request->cliente_domicilio) 
    || is_null($request->cliente_domicilio) 
    || $request->cliente_domicilio == '';

$colonia = !isset($request->colonia) 
    || is_null($request->colonia) 
    || $request->colonia == '';

$cliente_ip = !isset($request->cliente_ip) 
    || is_null($request->cliente_ip) 
    || $request->cliente_ip == '' 
    && $request->metodo_bloqueo != 'PPPOE';

$cliente_mac = !isset($request->cliente_mac) 
    || is_null($request->cliente_mac) 
    || $request->cliente_mac == ''
    && $request->metodo_bloqueo != 'PPPOE';

$cliente_tipo = !isset($request->cliente_tipo) 
    || is_null($request->cliente_tipo) 
    || $request->cliente_tipo == '';

$tipo_servicio = !isset($request->tipo_servicio) 
    || is_null($request->tipo_servicio) 
    || $request->tipo_servicio == '';

$cliente_paquete = !isset($request->cliente_paquete) 
    || is_null($request->cliente_paquete) 
    || $request->cliente_paquete == '';

$modem_instalado = !isset($request->modem_instalado) 
    || is_null($request->modem_instalado) 
    || $request->modem_instalado == '';

$serie_modem = !isset($request->serie_modem) 
    || is_null($request->serie_modem) 
    || $request->serie_modem == '';

$cliente_instalacion = !isset($request->cliente_instalacion) 
    || is_null($request->cliente_instalacion) 
    || $request->cliente_instalacion == '';

$cliente_corte = !isset($request->cliente_corte) 
    || is_null($request->cliente_corte) 
    || $request->cliente_corte == '';

$precio_instalacion = !isset($request->precio_instalacion) 
    || is_null($request->precio_instalacion) 
    || $request->precio_instalacion == '';

$mensualidad = !isset($request->mensualidad) 
    || is_null($request->mensualidad) 
    || $request->mensualidad == '';

$costo_renta = !isset($request->costo_renta) 
    || is_null($request->costo_renta) 
    || $request->costo_renta == '';


if (
    $cliente_nombres
    || $cliente_apellidos
    // || $cliente_email
    || $cliente_telefono
    || $cliente_domicilio
    || $colonia
    || $cliente_ip
    || $cliente_mac
    || $cliente_tipo
    || $tipo_servicio
    || $cliente_paquete
    || $modem_instalado
    || $serie_modem
    || $cliente_instalacion
    || $cliente_corte
    || $precio_instalacion
    || $mensualidad
    || $costo_renta
) {
    Flight::json([
        "status" => 400,
        "title" => "Solicitud incorrecta!",
        "details" => "Nombres, Apellidos, Email, Telefono, Domicilio, Colonia, IPv4, MAC, AP, Tipo, Servicio, Paquete, Modem, Serie, Antena, Fecha de instalacion, Corte, Mensualidad y Estado del equipo son requeridos!"
    ]);
} 

if (
    !$cliente_instalacion
    && !$cliente_apellidos
    && !$precio_instalacion
    // && !$cliente_email
    && !$cliente_telefono
    && !$cliente_domicilio
    && !$colonia
    && !$cliente_ip
    && !$cliente_mac
    && !$cliente_tipo
    && !$tipo_servicio
    && !$cliente_paquete
    && !$modem_instalado
    && !$serie_modem
    && !$cliente_corte
    && !$cliente_nombres
    && !$mensualidad
    && !$costo_renta
) {
    // Is registered user?
    $login = new Login();
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    }

    if ($is_user) {
        $customer = new Customer($request, $files);
        $create = $customer->create($usuario_id);

        if (!$create) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $customer->error_message,
                "data" => $request
            ]);
        } 

        if ($create) {
            Flight::json([
                "status" => 200,
                "title" => "Creado!",
                "details" => "El cliente fue agregado correctamente!",
                "data" => $customer->get_customer_by_id(),
                "metodo_bloqueo" => $customer->metodo_bloqueo,
                "leases" => $customer->leases,
                "arp" => $customer->arp,
                "queues" => $customer->queues,
                "pppoe" => $customer->pppoe,
                "firewall" => $customer->firewall,
                "whatsapp" => $customer->whatsapp
            ]);
        }
    }
}