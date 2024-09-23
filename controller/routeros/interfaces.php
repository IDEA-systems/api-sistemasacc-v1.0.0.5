<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);

if (!$is_user) {
    Flight::halt(500, "Error interno");
}

if ($is_user) {
    $mikrotik = new ReadMikrotik();
    $data = $mikrotik->get_mikrotik_by_id($mikrotik_id);

    // No se encontro ningun mikrotik
    if (empty($data)) {
        Flight::halt(500, "Error interno");
    }

    // Si se encontro, conectar
    if (!empty($data)) {
        $conn = new Mikrotik(
            $data[0]["mikrotik_ip"],
            $data[0]["mikrotik_usuario"],
            $data[0]["mikrotik_password"],
            $data[0]["mikrotik_puerto"]
        );

        if (!$conn->connected) {
            Flight::halt(500);
        }

        if ($conn->connected) {
            $array = $conn->comm("/interface/getall");
            Flight::json($array);
        }
    }
}