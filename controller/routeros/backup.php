<?php

$no_content = !isset($request['mikrotik_id']) || $request['mikrotik_id'] == "";

if ($no_content) {
    Flight::halt(400, "Solicitud incorrecta!");
}