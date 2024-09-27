<?php

class Process extends Messenger
{

    public $negociation;
    public $freemonth;
    public $automatical_suspencion;
    public $alerts_suspended;
    public $suspended_negociation;
    public $authorization_suspended;

    public $error;
    public $error_message;
    public $suspension;
    public $morosos;


    public function __construct()
    {
        $this->config = parse_ini_file('./config.ini', true);
    }


    /**
     * testWhatsapp
     *
     * @return mixed
     */
    public function testWhatsapp($usuario_id)
    {
        $phone = $this->get_brand();
        $phone = $phone[0]["codigo_pais"] . $phone[0]["phone"];
        $array = [ "phone" => $phone, "message" => "$usuario_id: Connected" ];
        return $this->whatsapp($array, $usuario_id, 'test');
    }


    public function get_customer($cliente_id) 
    {
        $SQL = "SELECT cliente_telefono FROM clientes WHERE cliente_id = ?";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function send_voucher($periodos, $cliente_id)
    {
        $brand = $this->get_brand();
        $cliente = $this->get_customer($cliente_id);
        $message = [
            "phone" => $brand[0]['codigo_pais'] . $cliente[0]['cliente_telefono'], 
            "message" => "*Adjuntamos su comprobante de pago*",
            // "mediaUrl" => "$this->config['URL_FRONTEND']/assets/clientesimg/pagos/$cliente_id".$periodos.".pdf"
        ];
        $this->whatsapp($message, $cliente_id, "comprobante");
    }

    /**
     * authorization_suspended
     * Verificar si se suspenden los clientes en autorizacion que no tengan pago actual
     * @return number
     */
    public function authorization_suspended()
    {
        $SQL = "SELECT status FROM configuration WHERE id = 6";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows[0]["status"];
    }


    /**
     * get_to_periods
     *
     * @return array
     */
    public function get_to_periods()
    {
        $SQL = "SELECT DATE(CURRENT_DATE) AS to_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[0]["to_date"]);
        $periodo_actual = $fecha[1] . $fecha[0];
        $periodos = array($periodo_actual);
        return $periodos;
    }

    /**
     * get_after_periods
     * Obtener los periodos posteriores
     * @return array
     */
    public function get_after_periods()
    {
        $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL +1 MONTH)) AS after_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[ 0 ][ "after_date" ]);
        $periodo_posterior = $fecha[ 1 ] . $fecha[ 0 ];
        $periodos = array($periodo_posterior);
        return $periodos;
    }


    /**
     * get_before_periods
     * Obtener el periodo anterior
     * @return array
     */
    public function get_before_periods()
    {
        $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL -1 MONTH)) AS before_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[ 0 ][ "before_date" ]);
        $periodo_anterior = $fecha[ 1 ] . $fecha[ 0 ];
        $periodos = array($periodo_anterior);
        return $periodos;
    }


    public function clients_with_completed_trading()
    {
        $periods = $this->get_to_periods();
        $IN_PERIODS = $this->transform_array_string_in_sql($periods);

        $query = Flight::gnconn()->prepare("
            SELECT 
                negociaciones.id_negociacion,
                negociaciones.fecha_fin,
                clientes.cliente_id,
                clientes.cliente_nombres,
                clientes.cliente_apellidos,
                clientes.cliente_telefono,
                clientes.metodo_bloqueo,
                clientes.interface_arp,
                clientes.server,
                clientes.profile,
                clientes.user_pppoe,
                clientes.password_pppoe,
                clientes.cliente_ip,
                clientes_servicios.suspender,
                mikrotiks.mikrotik_ip,
                mikrotiks.mikrotik_usuario,
                mikrotiks.mikrotik_password,
                mikrotiks.mikrotik_puerto,
                mikrotiks.mikrotik_nombre,
                mikrotiks.mikrotik_id,
                colonias.nombre_colonia,
                colonias.colonia_id
            FROM clientes 
            LEFT JOIN negociaciones 
            ON clientes.cliente_id = negociaciones.cliente_id 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id
            INNER JOIN mikrotiks
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id
            WHERE DATE(negociaciones.fecha_fin) <= DATE(CURRENT_DATE())
            AND DATEDIFF(CURRENT_DATE(), negociaciones.fecha_fin) >= 0
            AND negociaciones.status_negociacion = 1
            AND clientes_servicios.cliente_status = 4
            AND NOT EXISTS ( 
                SELECT pagos.pago_id FROM pagos 
                WHERE pagos.cliente_id = clientes_servicios.cliente_id 
                AND pagos.periodo_id 
                IN $IN_PERIODS 
                AND pagos.status_pago IN(1,2)
            )
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function change_status_customer($cliente_id, $cliente_status)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios 
                SET cliente_status = ? 
                WHERE cliente_id = ?
            ");
            $query->execute([
                $cliente_status,
                $cliente_id
            ]);
        } catch (Exception $error) {
            
        }
    }


    public function process_negociation()
    {
        $is_pending = $this->pending_process('negociacion');
        if (!$is_pending) return;

        $customers = $this->clients_with_completed_trading();
        $template = $this->get_templates('negociacion_terminada');
        $brand = $this->get_brand();

        $suspended = "";

        if (!empty($customers)) {
            $this->suspended_negociation = $this->config_status(4);
            $cliente_status = $this->suspended_negociation == 1 ? 2 : 1;

            foreach ($customers as $customer) {
                $nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];
                $message = preg_replace(['{{cliente}}', '{{fecha_fin}}'], [$nombres, $customer["fecha_fin"]], $template[0]["template"]);
                $data = [ "phone" => $brand[0]["codigo_pais"].$customer["cliente_telefono"], "message" => $message ];
                $this->whatsapp($data, $customer['cliente_id'], 'end_negociation');
                $this->change_status_customer($customer['cliente_id'], $cliente_status);
                $this->change_status_negociation($customer['cliente_id'], $customer['id_negociacion']);
                $suspended .= "$nombres : ".$customer["nombre_colonia"]."\n";
                $this->disabled_service($customer);
            }

            $params = ["phone" => $brand[0]["codigo_pais"].$brand[0]["phone"], "message" => "*Negociaciones suspendidas*\n\n".$suspended];
            $this->whatsapp($params, 'ADMIN', 'negociation_finally');
        }
        $this->end_process('negociacion');
    }

        
    /**
     * change_status_negociation
     *
     * @return void
     */
    public function change_status_negociation(string $cliente_id, int $id_negociacion) : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE negociaciones 
                SET status_negociacion = 3 
                WHERE cliente_id = ?
                AND id_negociacion = ?
            ");
            $query->execute([
                $cliente_id,
                $id_negociacion
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Las negociaciones no se finalizaron!";
        }
    }


    /**
     * finish_free_month
     *
     * @return mixed
     */
    public function finish_free_month()
    {
        $is_executed = $this->pending_process('freemonth');
        if (!$is_executed) return;
        
        try {
            // end month free //
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios 
                SET cliente_status = 1 WHERE EXISTS(
                    SELECT clientes.cliente_id 
                    FROM clientes 
                    WHERE DATEDIFF(CURRENT_DATE(), clientes.cliente_instalacion) >= 30 
                    AND clientes.cliente_id = clientes_servicios.cliente_id 
                ) AND cliente_status = 6
            ");
            $query->execute();
            $this->end_process('freemonth');
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al finalizar el mes gratis";
        }
    }


    public function pending_process($module)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM process_db 
            WHERE module = ? 
            AND DATE(last_send) = CURRENT_DATE
        ");
        $query->execute([ $module ]);
        $rows = $query->fetchAll();
        $is_pending = empty($rows);
        return $is_pending;
    }


    /**
     * config_status
     *
     * @return number
     */
    public function config_status($id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT status 
            FROM configuration 
            WHERE id = ?
        ");
        $query->execute([ $id ]);
        $rows = $query->fetchAll();
        return $rows[0]["status"];
    }


    /**
     * end_process
     *
     * @param  mixed $module
     * @return void
     */
    public function end_process($module)
    {
        $query = Flight::gnconn()->prepare("
            UPDATE process_db 
            SET last_send = CURRENT_DATE 
            WHERE module = ?
        ");
        $query->execute([ $module ]);
    }


    /**
     * get_customers_in_billing
     *
     * @return array
     */
    public function get_customers_in_billing()
    {
        $periods = $this->get_to_periods();
        $IN_PERIODS = $this->transform_array_string_in_sql($periods);

        $SQL = "
            SELECT 
                clientes.cliente_id,
                clientes.cliente_nombres,
                clientes.cliente_apellidos,
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, 
                clientes.cliente_telefono, 
                clientes_servicios.cliente_corte, 
                cortes_servicio.dia_pago 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN cortes_servicio 
            ON clientes_servicios.cliente_corte = cortes_servicio.corte_id 
            WHERE NOT EXISTS(
                SELECT pagos.pago_id FROM pagos 
                WHERE pagos.cliente_id = clientes.cliente_id 
                AND pagos.periodo_id IN $IN_PERIODS 
                AND pagos.status_pago != 0
            )
            AND clientes_servicios.cliente_corte = DAY(CURRENT_DATE) 
            AND clientes_servicios.cliente_status IN (1,2,4,6) 
            AND clientes_servicios.suspender != 0
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }



    /**
     * get_customers_in_alert
     *
     * @param  mixed $periods
     * @return array
     */
    public function get_customers_in_alert($periods)
    {
        $IN_PERIODS = $this->transform_array_string_in_sql($periods);
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.cliente_id, 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, 
                clientes.cliente_telefono, 
                clientes_servicios.cliente_corte, 
                cortes_servicio.dia_pago 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN cortes_servicio 
            ON clientes_servicios.cliente_corte = cortes_servicio.corte_id 
            WHERE NOT EXISTS (
                SELECT pagos.pago_id FROM pagos 
                WHERE pagos.cliente_id = clientes.cliente_id 
                AND pagos.periodo_id IN $IN_PERIODS
            ) 
            AND cortes_servicio.dia_comienzo = DAY(CURRENT_DATE) 
            AND clientes_servicios.suspender != 0
            AND clientes_servicios.cliente_status IN (1,2,4,5)
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * send_billing_whatsapp
     *
     * @return void
     */
    public function send_billing_whatsapp()
    {
        $customers = $this->get_customers_in_billing();
        $template = $this->get_templates('facturacion');
        $brand = $this->get_brand();
        foreach ($customers as $customer) {
            $phone = $brand[0]['codigo_pais'].$customer["cliente_telefono"];
            $nombres = $customer[ "nombres" ];
            $message = preg_replace([ '{{cliente}}' ], [ $nombres ], $template[ 0 ][ "template" ]);
            $data = array("phone" => $phone, "message" => $message);
            $this->whatsapp($data, $customer['cliente_id'], 'billing');
        }
    }



    /**
     * send_reminder_whatsapp
     *
     * @param  mixed $periods
     * @return void
     */
    public function send_reminder_whatsapp($periods)
    {
        $customers = $this->get_customers_in_alert($periods);
        $template = $this->get_templates('alertas');
        $brand = $this->get_brand();
        foreach ($customers as $customer) {
            $phone = $brand[0]["codigo_pais"].$customer["cliente_telefono"];
            $nombres = $customer["nombres"];
            $periodo = $periods[0];
            $message = preg_replace(['{{cliente}}', '{{periodos}}'], [$nombres, $periodo], $template[0]["template"]);
            $data = array("phone" => $phone, "message" => $message);
            $this->whatsapp($data, $customer['cliente_id'], 'reminder');
        }
    }


    /**
     * send_reminder_message
     *
     * @return void
     */
    public function send_reminder_message()
    {
        $last = $this->pending_process("reminder");
        if (!$last) return;

        $dias_finales = array(29, 30, 31);  // Dias finales del mes
        $today = intval(date("d"));

        $alert_previus_payment = $this->config_status(7);
        if ($alert_previus_payment == 0) return;

        $periods = in_array($today, $dias_finales)
            ? $this->get_after_periods()
            : $this->get_to_periods();

        $this->send_reminder_whatsapp($periods);
        $this->end_process("reminder");
    }

    /**
     * send_billing_messages
     *
     * @return void
     */
    public function send_billing_messages()
    {
        $last = $this->pending_process("billing");
        if (!$last) return;

        $get_config_billing = $this->config_status(8);
        if ($get_config_billing == 0) return;

        $this->send_billing_whatsapp();
        $this->end_process("billing");
    }


    /**
     * layoff_customers             Cambiar a status {2} = [suspendido]
     *
     * @return mixed                Array de clientes suspendidos
     **/
    public function layoff_customers()
    {
        $is_pending = $this->pending_process('suspension');
        if (!$is_pending) {
            $this->suspension = true;
            return $this->suspension;
        }

        $cuts = $this->get_all_cuts();

        foreach ($cuts as $cortes) {
            $day_end = [1, 2];
            $periods = in_array($cortes["dia_terminacion"], $day_end)
                ? $this->get_before_periods()
                : $this->get_to_periods();
            $this->layoff_in_database($periods, $cortes["dia_pago"]);

            if (!$this->error) continue;
            if ($this->error) {
                $this->suspension = false;
                return $this->suspension;
            }
        }

        $this->end_process('suspension');
        $this->suspension = true;
        return $this->suspension;
    }

    
    /**
     * get_all_cuts
     *
     * @return array
     */
    public function get_all_cuts()
    {
        $SQL = "SELECT * FROM cortes_servicio WHERE dia_terminacion <= DAY(CURRENT_DATE)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }
    
    /**
     * get_customers_suspended
     *
     * @return array
     */
    public function get_customers_suspended() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.*, 
                mikrotiks.* 
            FROM clientes 
            INNER JOIN clientes_servicios
            ON clientes.cliente_id = clientes_servicios.cliente_id
            INNER JOIN colonias
            ON clientes_servicios.colonia = colonias.colonia_id
            INNER JOIN mikrotiks
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id
            WHERE clientes_servicios.cliente_status = 2
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }



    public function disabled_service($customer) {
        $cliente_nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];

        $conn = new Mikrotik(
            $customer["mikrotik_ip"],
            $customer["mikrotik_usuario"],
            $customer["mikrotik_password"],
            $customer["mikrotik_puerto"]
        );

        if (!$conn->connected) {
            $this->error = true;
            $this->error_message = "Mikrotik error " . $customer["mikrotik_nombre"];
            $this->morosos = false;
            return $this->morosos;
        }

        if ($conn->connected) {
            switch($customer['metodo_bloqueo']) {
                case 'DHCP':
                    $conn->add_from_address_list(
                        $cliente_nombres,
                        $customer['cliente_ip'],
                        "MOROSOS"
                    );
                    $conn->disconnect();
                break;
    
                case 'ARP':
                    $conn->add_from_address_list(
                        $cliente_nombres,
                        $customer['cliente_ip'],
                        "MOROSOS"
                    );
                    $conn->disconnect();
                break;
    
                case 'PPPOE':
                    $conn->disabled_secret(
                        $customer['user_pppoe'],
                        $customer['profile']
                    );
    
                    $this->morosos = $conn->remove_customer_actives(
                        $customer["user_pppoe"],
                        $customer['profile']
                    );
                    $conn->disconnect();
                break;
                default: // NO HACER NADA
            }
        }
    }


    
    /**
     * suspension
     *
     * @return mixed
     */
    public function suspension()
    {
        $customers = $this->get_customers_suspended();
        foreach ($customers as $customer) {
            $this->disabled_service($customer);
        }
    }

    /**
     * transform_array_string_in_sql
     * Convertir array a sentencia IN de SQL
     * @param  mixed $periods
     * @return string
     */
    public function transform_array_string_in_sql($periods)
    {
        $text = json_encode($periods);
        $order = array('"', '[', ']');
        $replace = array("'", "(", ")");
        $in = str_replace($order, $replace, $text);
        return $in;
    }



    /**
     * get_current_suspended
     *
     * @param  mixed $periods
     * @param  mixed $corte_id
     * @return array
     */
    public function get_current_suspended($periodos, $corte_id)
    {
        $IN_PERIODS = $this->transform_array_string_in_sql($periodos);
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.cliente_nombres,
                clientes.cliente_apellidos, 
                clientes.cliente_id, 
                clientes.cliente_telefono, 
                clientes_servicios.cliente_corte, 
                colonias.nombre_colonia 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id 
            INNER JOIN cortes_servicio 
            ON clientes_servicios.cliente_corte = cortes_servicio.corte_id 
            WHERE NOT EXISTS(
                SELECT pagos.pago_id FROM pagos 
                WHERE pagos.cliente_id = clientes_servicios.cliente_id 
                AND pagos.periodo_id IN $IN_PERIODS
            ) AND clientes_servicios.cliente_status IN (1,2,5) 
            AND clientes_servicios.cliente_corte = $corte_id
            AND clientes_servicios.suspender = 1
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * send_suspension_messages
     * Enviar mensajes a clientes suspendidos
     * @return bool
     */
    public function send_suspension_messages()
    {
        $is_pending = $this->pending_process('suspended');
        if (!$is_pending) return true;

        $cuts = $this->get_all_cuts();

        foreach ($cuts as $cortes) {
            $day_end = [1, 2];
            $periods = in_array($cortes["dia_terminacion"], $day_end)
                ? $this->get_before_periods()
                : $this->get_to_periods();

            $customers = $this->get_current_suspended($periods, $cortes["dia_pago"]);
            $template = $this->get_templates('suspension');
            $brand = $this->get_brand();

            $list = "";
            foreach ($customers as $customer) {
                $phone = $customer["cliente_telefono"];
                $nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];
                $colonia = $customer["nombre_colonia"];
                $corte = $customer["cliente_corte"];
                $message = preg_replace( ['{{cliente}}', '{{corte}}'], [$nombres, $corte], $template[0]["template"] );
                $phone = $brand[0]["codigo_pais"].$phone;
                $options = ["phone" => $phone, "message" => $message];
                $this->whatsapp( $options, $customer['cliente_id'], 'suspension' );
                $list .= "$nombres : $colonia\n";
            }
            
            if (!empty($list)) {
                $brand_phone = $brand[0]["codigo_pais"].$brand[0]["phone"];
                $params = array("phone" => $brand_phone, "message" => "*Clientes suspendidos*\n\n".$list);
                $this->whatsapp($params, 'ADMIN', 'suspension');
            }
        }

        $this->end_process('suspended');
        $this->suspension = true;
        return $this->suspension;
    }


    /**
     * layoff_in_database
     * Suspender a clientes morosos
     * @param  mixed $periodo_id
     * @param  mixed $corte_id
     * @return void
     */
    public function layoff_in_database($periods, $corte_id)
    {
        $this->authorization_suspended = $this->authorization_suspended();

        // Solo suspender si esta activa la opcion en la configuracion del sistema 
        $IN_PERIODS = $this->transform_array_string_in_sql($periods);  // String SQL (IN)
        
        $status = $this->authorization_suspended == 1 ? [1,5] : [1];
        $IN_STATUS = $this->transform_array_string_in_sql($status);

        try {
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios 
                SET cliente_status = 2 
                WHERE NOT EXISTS(
                    SELECT pagos.pago_id FROM pagos 
                    WHERE pagos.cliente_id = clientes_servicios.cliente_id 
                    AND pagos.periodo_id IN $IN_PERIODS
                    AND pagos.status_pago IN (1,2)
                ) 
                AND cliente_status IN $IN_STATUS
                AND cliente_corte = $corte_id
                AND cliente_paquete != 0
                AND suspender = 1
            ");
            $query->execute();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al suspender en la base de datos";
        }
    }


    /**
     * layoff_in_mikrotik
     *
     * @param  mixed $customers
     * @return mixed
     */
    public function layoff_in_mikrotik()
    {
        $this->automatical_suspencion = $this->config_status(2);

        if ($this->automatical_suspencion == 0) {
            $this->morosos = true;
            return $this->morosos;
        }

        $is_pending = $this->pending_process('morosos');

        if (!$is_pending) {
            $this->morosos = true;
            return $this->morosos;
        }

        $this->suspension();
        $this->end_process('morosos');
    }

    
    /**
     * change_priority_failures
     *
     * @return void
     */
    public function change_priority_failures() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE reportes_fallas SET prioridad = 1
                WHERE DATE(fecha_atencion) <= DATE(CURRENT_DATE())
                AND status IN (1,2)
                AND prioridad IN (2,3)
            ");
            $query->execute();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al verificar las fallas";
        }
    }

    public function mikrotik_backup()
    {
        $is_backed_up = $this->pending_process('mikrotik');
        if (!$is_backed_up) return;

        $mikrotiks = $this->get_all_mikrotik_active();
        foreach($mikrotiks as $mikrotik) {
            $address = $mikrotik['mikrotik_ip'];
            $usuario = $mikrotik['mikrotik_usuario'];
            $password = $mikrotik['mikrotik_password'];
            $puerto = $mikrotik['mikrotik_puerto'];
            $conn = new Mikrotik($address, $usuario, $password, $puerto);
            if ($conn->connected) {
                $conn->comm("/system/backup/save", [
                    "name" => "Backup-" . date("Y-m-d:h_m:s")
                ]);
            }
        }
        $this->end_process('mikrotik');
    }

    public function get_all_mikrotik_active()
    {
        $SQL = "
            SELECT 
                mikrotik_ip,
                mikrotik_usuario,
                mikrotik_password,
                mikrotik_puerto
            FROM mikrotiks
            WHERE mikrotik_status = 1
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }
}
