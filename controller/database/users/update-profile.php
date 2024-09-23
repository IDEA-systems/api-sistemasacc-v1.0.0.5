<?php

$request = Flight::request()->data;
$files = Flight::request()->files;

$username = isset($request->usuario_nombre) &&
    !empty($request->usuario_nombre) &&
    strlen($request->usuario_nombre) >= 5;

$email = isset($request->empleado_email) &&
    !empty($request->empleado_email);

$telefono = isset($request->empleado_telefono) &&
    !empty($request->empleado_telefono) &&
    strlen($request->empleado_telefono) == 10;

$nombres = isset($request->empleado_nombre) &&
    !empty($request->empleado_nombre);

$apellidos = isset($request->empleado_apellido) &&
    !empty($request->empleado_apellido);

if (
    !$username || 
    !$email || 
    !$telefono || 
    !$nombres || 
    !$apellidos
) {
    Flight::json([
        "status" => 400,
        "title" => "Solicitud incorrecta!",
        "details" => "Asegurese de enviar datos correctos!"
    ]);
}

if (
    $username && 
    $email && 
    $telefono && 
    $nombres && 
    $apellidos
) {
    $login = new Login($usuario_id);
    $is_user = $login->is_user($usuario_id);

    if (!$is_user) {
        Flight::halt(401, "Usuario no autorizado!");
    }

    if ($is_user) {
        $user = new User($request, $files);
        $user->update_user($files);
        
        if ($user->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $user->error_message
            ]);
        }

        else if ($user->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $user->error_message
            ]);
        }

        else {
            Flight::json([
                "status" => 200,
                "title" => "Correcto!",
                "details" => "Datos modificados",
                "data" => $user->get_user_by_id()
            ]);
        }
    }
}