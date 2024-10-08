<?php

$login = new Login();
$is_user = $login->is_user($usuario_id);
if (!$is_user) {
    Flight::halt(401, "No autorizado!");
}

if ($is_user) {
    /**
     * Instancia de la clase Process
     * 
     * @var mixed
     */
    $process = new Process();

    /**
     * Verificar conexion de whatsapp
     * 
     * @var mixed
     */
    $whatsapp = $process->testWhatsapp($usuario_id);

    if ($whatsapp) {
        /**
         * Enviar mensajes de suspencion
         * 
         * @var mixed
         */
        $process->send_suspension_messages();

        if ($process->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $process->error_message,
            ]);
        }

        /**
         * Enviar mensajes de recordatorio
         * 
         * @var mixed
         */
        $process->send_reminder_message();

        if ($process->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $process->error_message,
            ]);
        }

        /**
         * Enviar mensajes de facturacion
         * 
         * @var mixed
         */
        $process->send_billing_messages();

        if ($process->error) {
            Flight::json([
                "status" => 500,
                "title" => "Error interno!",
                "details" => $process->error_message,
            ]);
        }

        if (!$process->error) {
            Flight::json([
                "status" => 200,
                "title" => "Proceso terminado!",
                "details" => "El proceso termino correctamente!",
            ]);
        }
    }

    if (!$whatsapp) {
        Flight::json([
            "status" => 500,
            "title" => "Error interno!",
            "details" => "No se pudo enviar los mensajes de suspencion, recordatorio y facturacion",
        ]);
    }
}