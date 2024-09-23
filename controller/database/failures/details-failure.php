<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user || $permisions[0]["reports"] == 0) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user && $permisions[0]["reports"] == 1) {
    $failures = new Failures();
    $failures->reporte_id = $reporte_id;
    $customer_fail = $failures->get_failure_by_id();
    Flight::json($customer_fail);
}