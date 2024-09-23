<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
} 
if ($is_user) {
    $filters = [
        'cliente_corte' => isset($_GET["corte_id"]) ? $_GET["corte_id"] : null,
        'cliente_status' => isset($_GET["status"]) ? $_GET["status"] : null,
        'cliente_id' => isset($_GET["cliente_id"]) ? $_GET["cliente_id"] : null,
        'corte_inicio' => isset($_GET["corte_id"]) ? $_GET["corte_id"]: null,
        'corte_fin' => isset($_GET["corte_id"]) ? $_GET["corte_id"] + 3 : null,
        'pagina' => isset($_GET["pagina"]) ? $_GET["pagina"] : null,
        'colonia' => isset($_GET["colonia"]) ? $_GET["colonia"] : null,
        'search' => isset($_GET["search"]) ? $_GET["search"] : null,
        'deudores' => isset($_GET["deudores"])
    ];
    $customers = new ReadCustomers($filters);
    $customers = $customers->clientes();
    $_SESSION["CLIENTS_EXPORTS"] = $customers;
    Flight::json($customers);
}