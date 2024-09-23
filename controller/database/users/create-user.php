<?php

$login = new Login($usuario_id);
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if ( 
    !$is_user || 
    !$is_root
) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ( 
    $is_user && 
    $is_root
) {
    $request = Flight::request()->data;
    $files = Flight::request()->files;
    $user = new User($request, $files);

    if (
        !isset($request->usuario_nombre) || 
        !isset($request->usuario_password) || 
        !isset($request->empleado_email) || 
        !isset($request->empleado_telefono) || 
        !isset($request->empleado_nombre) || 
        !isset($request->empleado_apellido)
    ) {
        Flight::json([
            "status" => 400,
            "title" => "Mala solicitud!",
            "details" => "Datos incorrectos!",
        ]);
    } 
    
    else {
        $user = new User($request, $files);
        $create = $user->create_user();
        $profile = $user->save_profile($files->usuario_perfil);
        $ine = $user->save_ine($files->empleado_ine);

        if (!$create) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $user->error_message
            ]);
        }
        
        if ($create) {
            Flight::json([
                "status" => 200,
                "title" => "Agregado!",
                "details" => "Agregado correctamente!",
                "data" => $user->get_user_by_id(),
                "files" => $profile,
                "ine" => $ine
            ]);
        }
    }
}