<?php

/** 
 * 
 * Valida si el usuario que solicita la lista de cortes es un usuario 
 * si no lo es retorna un status code: 401  en caso contrario
 * obtener la lista de cortes y retornarlos en un json
 * 
**/

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado");
}

if ($is_user) {
    $cuts = new ReadCuts();
    $cuts = $cuts->get_all_cuts();
    Flight::json($cuts);
}