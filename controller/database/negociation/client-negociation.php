<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 
    
if ($is_user) {
    $negociation = new ReadNegociation();
    $activa = $negociation->get_client_negociation($cliente_id);
    $mes = $negociation->get_negociation_to_month($cliente_id);
    $pagos = $negociation->get_payments_anterity($cliente_id);
    $date = $negociation->min_max_date_negociation();
    Flight::json([
        "status" => 200,
        "title" => "Procesado!",
        "details" => "El proceso se termino correctamente!",
        "data" => [
            "negociation_active" => $activa,
            "negociation_month" => $mes,
            "last_payment" => $pagos,
            "date" => $date
        ]
    ]);
}