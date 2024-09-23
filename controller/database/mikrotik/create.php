<?php
$request = Flight::request()->data;

$invalid_password = !isset ($request->mikrotik_password) ||
    $request->mikrotik_password == '';

$invalid_user = !isset ($request->mikrotik_usuario) ||
    $request->mikrotik_usuario == '';

$invalid_name = !isset ($request->mikrotik_nombre) ||
    $request->mikrotik_nombre == '';

$invalid_port = !isset ($request->mikrotik_puerto) ||
    $request->mikrotik_puerto == '';

$invalid_ip = !isset ($request->mikrotik_ip) ||
    $request->mikrotik_ip == '';


$bad_request = $invalid_password || 
    $invalid_user || 
    $invalid_name || 
    $invalid_port ||
    $invalid_ip;

if ($bad_request) {
    Flight::json([
        "status" => 400,
        "title" => "Solicitud incorrecta!",
        "details" => "Nombre, Usuario, Password, Puerto e IPv4 son requeridos!"
    ]);
}

if (!$bad_request) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    $is_root = $login->is_root($usuario_id);

    if (!$is_user || !$is_root) {
        Flight::halt(401, "Usuario no autorizado!");
    }

    else {
        $mikrotik = new Mikrotiks($request);
        $mikrotik->create();

        if ($mikrotik->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $mikrotik->error_message
            ]);
        }

        else if ($mikrotik->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $mikrotik->error_message
            ]);
        }

        else {
            $byid = $mikrotik->get_mikrotik_by_id();
            $mikrotiks = $mikrotik->get_all_mikrotiks();
            Flight::json([
                "status" => 200,
                "title" => "Creado!",
                "details" => "El mikrotik fue agregado correctamente!",
                "data" => $byid,
                "mikrotiks" => $mikrotiks,
                "accept" => $mikrotik->accept,
                "drop" => $mikrotik->drop,
                "redirect" => $mikrotik->redirect
            ]);
        }
    }
}