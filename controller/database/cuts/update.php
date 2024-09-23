<?php

/** 
 * 
 * ❓ Valida si el usuario que solicita la actualizacion sea valido y sea root
 * ❓ si no lo es retorna un status code: 401  en caso contrario
 * ❓ validar que los datos requeridos existan en la request
 * ❓ Si no estan devolver un json con los detalles
 * ❓ Si los datos requeridos si existen entonces realizar la actualización
 * 🚀 Devolver la respuesta con un status 200 y los datos nuevos
 * 
**/

$login = new Login();
$is_user = $login->is_user($usuario_id);
$is_root = $login->is_root($usuario_id);

if (!$is_user || !$is_root) {
    Flight::halt(401, "Usuario no autorizado!");
}

if ($is_user && $is_root) {
    $request = Flight::request()->data;

    $is_corte = isset($request->corte_id) 
        && !empty($request->corte_id);

    $is_comienzo = isset($request->dia_comienzo) 
        && !empty($request->dia_comienzo) 
        && $request->dia_comienzo != 0;

    $is_terminacion = isset($request->dia_terminacion) 
        && !empty($request->dia_terminacion) 
        && $request->dia_terminacion != 0;

    if (!$is_corte || !$is_comienzo || !$is_terminacion) {
        Flight::json([
            "status" => 400,
            "title" => "Solicitud incorrecta!",
            "details" => "Día de pago, día de inicio, día de terminación son requeridos!"
        ]);
    }

    if ($is_corte && $is_comienzo && $is_terminacion) {
        $cuts = new Cuts($request);
        $cuts->update();

        if ($cuts->error) {
            Flight::halt(500, $cuts->error_message);
        }

        if ($cuts->conflict) {
            Flight::json([
                "status" => 409,
                "title" => "Conflicto!",
                "details" => $cuts->error_message
            ]);
        }

        if (!$cuts->error && !$cuts->conflict) {
            Flight::json([
                "status" => 200,
                "title" => "Correcto!",
                "details" => "Actualizado correctamente",
                "data" => $cuts->get_cut_by_id($request->corte_id)
            ]);
        }
    }
}