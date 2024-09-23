<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user || !$is_root) {
    Flight::halt(401);
}

if ($is_user) {
    $request = Flight::request()->data;
    
    $exists_name = !isset($request->nombre_paquete) 
        ||  empty($request->nombre_paquete);

    $exists_banda_ancha = !isset($request->ancho_banda)
        || empty($request->ancho_banda);

    $is_price = !isset($request->precio)
        || !is_numeric($request->precio)
        || empty($request->precio);

    if ($exists_name || $exists_banda_ancha || $is_price) {
        Flight::halt(400, "Bad request!");
    } 
    
    if (!$exists_name && !$exists_banda_ancha && !$is_price) {
        $packages = new Packages($request);
        $packages->update_package();

        if ($packages->error) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto",
                "details" => $packages->error_message
            ]);
        }

        if (!$packages->error) {
            Flight::json([
                "status" => 200,
                "title" => "Correcto!",
                "details" => "Paquete actualizado correctamente",
                "data" => $packages->get_package_by_id(),
                "paquetes" => $packages->get_all_packages()
            ]);
        }
    }
}