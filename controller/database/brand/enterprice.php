<?php

$login = new Login();
$is_root = $login->is_root($usuario_id);
if (!$is_root) {
    Flight::halt(401);
}
if ($is_root) {
    $request = Flight::request()->data;
    $files = Flight::request()->files;

    $exists_name = isset($request->name)
        || !empty($request->name);

    $exists_email = isset($request->email)
        || !empty($request->email);

    $exists_phone = isset($request->phone)
        || !empty($request->phone);

    // Not exists data required
    if (!$exists_name || !$exists_email || !$exists_phone) {
        Flight::halt(400);
    }

    if ( $exists_name && 
        $exists_email && 
        $exists_phone
    ) {
        $enterprice = new Brand($request, $files);
        $enterprice->update_enterprice_info();
        $enterprice->save_img_brand($files);

        if ($enterprice->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $enterprice->error_message
            ]);
        } 
        
        else {
            Flight::json([
                "status" => 200,
                "title" => "Correcto!",
                "details" => "Información agregada correctamente!"
            ]);
        }
    }
}