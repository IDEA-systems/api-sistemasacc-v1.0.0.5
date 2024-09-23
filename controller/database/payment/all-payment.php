<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 

if ($is_user) {
    $status_pago = isset($_GET['status_pago']) ? $_GET['status_pago'] : null;
    $pago_folio = isset($_GET['pago_folio']) ? $_GET['pago_folio'] : null;
    $tipo_pago = isset($_GET['tipo_pago']) ? $_GET['tipo_pago'] : null;
    $cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : null;
    $periodo_id = isset($_GET['periodo_id']) ? $_GET['periodo_id'] : null;
    $usuario_captura = isset($_GET['usuario_captura']) ? $_GET['usuario_captura'] : null;
    $date_start = isset($_GET['date_start']) ? $_GET['date_start'] : null;
    $date_end = isset($_GET['date_end']) ? $_GET['date_end'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $all = isset($_GET['all']) ? $_GET['all'] : false;

    $filters = [
        "status_pago" => $status_pago,
        "pago_folio" => $pago_folio,
        "tipo_pago" => $tipo_pago,
        "cliente_id" => $cliente_id,
        "periodo_id" => $periodo_id,
        "usuario_captura" => $usuario_captura,
        "date_start" => $date_start,
        "date_end" => $date_end,
        "search" => $search,
        "all" => $all
    ];

    $payment = new Payment();
    $rows = $payment->get_all_payments($filters);
    Flight::json($rows);
}