<?php

$request = Flight::request()->data;       // Client data
$login = new Login($request);             // Created new session
$user = $login->search_user();

$status = isset($user[0]["usuario_status"]) 
    ? $user[0]["usuario_status"] 
    : false;

$password = isset($user[0]["usuario_password"]) && 
    $login->password_verify(
        $request->user_password, 
        $user[0]["usuario_password"]
    );

if (!$status || !$password) {
    Flight::json([
        "status" => 204,
        "title" => "Datos incorrectos!",
        "details" => "Nombre de usuario o contraseña incorrectos!"
    ]);
} 

else if ($status == 2) {
    Flight::json([
        "status" => 204,
        "title" => "Usuario no disponible!",
        "details" => "El usuario ha sido temporalmente suspendido!"
    ]);
}

else if ($status == 3) {
    Flight::json([
        "status" => 204,
        "title" => "Usuario no disponible!",
        "details" => "El usuario esta bloqueado!"
    ]);
}

else {
    Flight::json([
        "status" => 200,
        "title" => "Datos correctos!",
        "details" => "Ha iniciado session en el sistema!",
        "data" => $user
    ]);
}