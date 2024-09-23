<?php

class Negociation extends Messenger
{
    public $id_negociacion;
    public $negociacion;
    private $cliente_id;
    private $fecha_inicio;
    private $fecha_fin;
    private $comentario;
    private $status_negociacion;
    private $usuario_negociacion;
    public $is_suitable;
    public $error;
    public $error_message;
    public $activacion;

    public function __construct($request = [])
    {
        $this->fecha_inicio = date('Y-m-d');
        $this->fecha_fin = isset($request->fecha_fin) ? $request->fecha_fin : null;
        $this->cliente_id = isset($request->cliente_id) ? $request->cliente_id : null;
        $this->comentario = isset($request->comentario) ? $request->comentario : '';
        $this->status_negociacion = isset($request->status_negociacion) ? $request->status_negociacion : 1;
        $this->usuario_negociacion = isset($request->usuario_id) ? $request->usuario_id : '';
    }

    public function __get($propiedad)
    {
        if (property_exists($this, $propiedad)) {
            return $this->$propiedad;
        }
    }

    public function __set($propiedad, $valor)
    {
        if (property_exists($this, $propiedad)) {
            $this->$propiedad = $valor;
        }
    }

    public function get_client_negociation()
    {
        $SQL = "SELECT * FROM negociaciones WHERE cliente_id = '$this->cliente_id' AND status_negociacion = 1";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return !empty($rows);
    }

    public function get_negociation_to_month()
    {
        $SQL = "SELECT * FROM negociaciones WHERE cliente_id = '$this->cliente_id' AND MONTH(fecha_inicio) = MONTH(CURRENT_DATE()) AND YEAR(CURRENT_DATE()) = YEAR(fecha_inicio)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return !empty($rows);
    }


    public function get_payments_anterity()
    {
        $periods = $this->get_to_periods();
        $text = json_encode($periods);
        $order = array('"', '[', ']');
        $replace = array("'", "(", ")");
        $IN = str_replace($order, $replace, $text);

        $SQL = "SELECT pago_id, periodo_id, cliente_id FROM pagos WHERE cliente_id = '$this->cliente_id' AND periodo_id IN $IN AND YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE())";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return empty($rows);
    }

    /**
     * get_to_periods
     *
     * @return array
     */
    public function get_to_periods() {
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
    public function get_after_periods() {
        $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL +1 MONTH)) AS after_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[0]["after_date"]);
        $periodo_posterior = $fecha[1] . $fecha[0];
        $periodos = array($periodo_posterior);
        return $periodos;
    }

        
    /**
     * get_before_periods
     * Obtener el periodo anterior
     * @return array
     */
    public function get_before_periods() {
        $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL -1 MONTH)) AS before_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[0]["before_date"]);
        $periodo_anterior = $fecha[1] . $fecha[0];
        $periodos = array($periodo_anterior);
        return $periodos;
    }


    public function days_corrects()
    {
        $SQL = "SELECT DATEDIFF(DATE('$this->fecha_fin'), DATE(CURRENT_DATE())) AS dias";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows[0]["dias"];
    }


    public function send_negociation_whatsapp()
    {
        $type_message = 'negociacion';
        $customer = $this->get_customer_by_id();
        $template = $this->get_templates($type_message);
        $brand = $this->get_brand();
        $nombres = $customer[0]["nombres"];
        $phone = $customer[0]["cliente_telefono"];
        $cliente_id = $customer[0]["cliente_id"];

        $message = "*".$template[0]["title"]."*" . "\n" . preg_replace(
            ['{{cliente}}','{{fecha}}'], 
            [$nombres, $this->fecha_fin], 
            $template[0]["template"]
        );

        $data = [
            "phone" => $brand[0]["codigo_pais"].$phone,
            "message" => $message
        ];

        $this->whatsapp($data, $cliente_id, $type_message);
    }


    /**
     * save_negociation
     * Agregar al base de datos 
     * @return mixed
     */
    public function save_negociation($is_root)
    {
        if ($is_root) {
            return $this->add_negociation_as_root();
        }

        if (!$is_root) {
            return $this->add_negociation_as_user_normal();
        }
    }


    public function add_negociation_as_root()
    {
        if ($this->days_corrects() > 90) {
            $this->error = true;
            $this->error_message = "Se exedieron los dias!";
            return false;
        }

        // Finalizar negociaciones actuales
        $this->end_negociations($this->cliente_id);

        // Agregar nueva negociacion
        $this->insert_new_negociation();
        if ($this->error) return false;

        // Cambiar el status del cliente
        $this->change_status();
        if ($this->error) return false;

        // Datos del mikrotik
        $rows = $this->get_mikrotik_credentials();
        $this->activate_service($rows);
        $this->send_negociation_whatsapp();
        return true;
    }


    public function end_negociations($cliente_id)
    {
        $SQL = "UPDATE negociaciones SET status_negociacion = 3 WHERE cliente_id = '$cliente_id'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
    }


    public function add_negociation_as_user_normal()
    {
        if ($this->days_corrects() > 30) {
            $this->error = true;
            $this->error_message = "Se exedieron los dias!";
            return false;
        }

        if ($this->get_client_negociation()) {
            $this->error = true;
            $this->error_message = "Negociación existente!";
            return false;
        }

        if ($this->get_negociation_to_month()) {
            $this->error = true;
            $this->error_message = "Negociacion mensual gastada!";
            return false;
        }

        $this->insert_new_negociation();
        if ($this->error) return false;

        $this->change_status();
        if ($this->error) return false;

        $rows = $this->get_mikrotik_credentials();
        $this->activate_service($rows);
        $this->send_negociation_whatsapp();
        return true;
    }


    public function get_mikrotik_credentials()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.cliente_id, 
                clientes.cliente_ip, 
                clientes.cliente_mac,
                clientes.cliente_email,
                clientes.server,
                clientes.interface_arp,
                clientes.profile,
                clientes.user_pppoe,
                clientes.password_pppoe,
                clientes.cliente_nombres,
                clientes.cliente_apellidos,
                clientes.metodo_bloqueo, 
                mikrotiks.mikrotik_id, 
                mikrotiks.mikrotik_ip, 
                mikrotiks.mikrotik_usuario, 
                mikrotiks.mikrotik_password, 
                mikrotiks.mikrotik_puerto 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            WHERE clientes.cliente_id = ?
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * insert_new_negociation
     *
     * @return void
     */
    public function insert_new_negociation() : void
    {
        try {
            $SQL = "INSERT INTO `negociaciones` VALUES (NULL, ?, ?, ?, ?, ?, ?)";
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute([
                $this->cliente_id,
                $this->fecha_inicio,
                $this->fecha_fin,
                $this->comentario,
                $this->status_negociacion,
                $this->usuario_negociacion
            ]);
            $this->id_negociacion = $this->get_new_id();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"No se pudo completar la negociación!";
        }
    }


    public function get_new_id()
    {
        $new_id = Flight::gnconn()->lastInsertId();
        return $new_id;
    }
    
    /**
     * change_status
     *
     * Cambiar el status del cliente
     * @return void
     */
    public function change_status()
    {
        try {
            $SQL = "
                UPDATE clientes_servicios 
                SET cliente_status = 4 
                WHERE cliente_id = ?
            ";
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute([ 
                $this->cliente_id 
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage(); //"Error al actualizar el status del cliente!";
        }
    }

    /**
     * activate_service
     *
     * @param  mixed $rows
     * @return void
     */
    public function activate_service($rows)
    {
        $mktIP = isset($rows[0]["mikrotik_ip"]) ? $rows[0]["mikrotik_ip"] : '';
        $user = isset($rows[0]["mikrotik_usuario"]) ? $rows[0]["mikrotik_usuario"] : '';
        $pass = isset($rows[0]["mikrotik_password"]) ? $rows[0]["mikrotik_password"] : '';
        $port = isset($rows[0]["mikrotik_puerto"]) ? $rows[0]["mikrotik_puerto"] : '';
        $metodo_bloqueo = isset($rows[0]["metodo_bloqueo"]) ? $rows[0]["metodo_bloqueo"] : false;

        $conn = new Mikrotik($mktIP, $user, $pass, $port);
        if (!$conn->connected) {
            $this->mikrotik_remove_fail($rows);
            $this->activacion = false;
            $conn->disconnect();
            return;
        }

        if ($metodo_bloqueo) {
            switch ($metodo_bloqueo) {
                case "DHCP":
                    $conn->remove_from_address_list(
                        $rows[0]['cliente_ip'],
                        "MOROSOS"
                    );
                    $this->activacion = true;
                    $conn->disconnect();
                break;

                case "ARP":
                    $conn->remove_from_address_list(
                        $rows[0]['cliente_ip'],
                        "MOROSOS"
                    );
                    $this->activacion = true;
                    $conn->disconnect();
                break;

                case "PPPOE":
                    $conn->enable_secret(
                        $rows[0]['user_pppoe'],
                        $rows[0]['profile']
                    );
                    $this->activacion = true;
                    $conn->disconnect();
                break;
                default: // Hacer nada
            }
        }
    }


    /**
     * mikrotik_remove_fail
     *
     * @param  mixed $mikrotik_data
     * @return void
     */
    public function mikrotik_remove_fail($mikrotik_data)
    {
        $validate_exists = $this->this_record_already_exists(
            'mikrotik_retry_remove', 
            $mikrotik_data[0]['cliente_id'], 
            'morosos'
        );

        !$validate_exists && $this->insert_into_mikrotik_retry_remove(
            $mikrotik_data[0]['cliente_id'], 
            $mikrotik_data[0]['mikrotik_id'], 
            $mikrotik_data[0]['cliente_ip'], 
            $mikrotik_data[0]['cliente_mac'], 
            $mikrotik_data[0]['server'], 
            $mikrotik_data[0]['interface_arp'], 
            $mikrotik_data[0]['profile'], 
            "MOROSOS", 
            "morosos"
        );
    }

    
    /**
     * this_record_already_exists
     *
     * @param  mixed $TABLE
     * @param  mixed $cliente_id
     * @param  mixed $module
     * @return bool
     */
    public function this_record_already_exists($TABLE, $cliente_id, $module)
    {
        $SQL = "SELECT * FROM $TABLE WHERE cliente_id = '$cliente_id' AND module = '$module'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $exists = empty($rows);
        if ($exists) return false;
        return true;
    }
    
    /**
     * insert_into_mikrotik_retry_remove
     *
     * @param  mixed $cliente_id
     * @param  mixed $cliente_ip
     * @param  mixed $cliente_mac
     * @param  mixed $module
     * @return void
     */
    public function insert_into_mikrotik_retry_remove($cliente_id, $mikrotik_id, $cliente_ip, $cliente_mac, $server, $interface_arp, $profile, $address_list, $module)
    {
        $query = Flight::gnconn()->prepare("
            INSERT INTO mikrotik_retry_remove 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, null)
        ");
        $query->execute([
            $cliente_id,
            $mikrotik_id,
            $cliente_ip,
            $cliente_mac,
            $server,
            $interface_arp,
            $profile,
            $address_list,
            $module
        ]);
    }

    
    /**
     * get_customer_by_id
     *
     * @return array
     */
    public function get_customer_by_id()
    {
        $SQL = "
            SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, clientes.*, clientes_servicios.*, tipo_servicios.nombre_servicio, colonias.nombre_colonia, colonias.mikrotik_control, mikrotiks.mikrotik_nombre, paquetes.nombre_paquete, modem.modelo as modem, clientes_status.status_id, clientes_status.nombre_status FROM clientes 
            INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id 
            LEFT JOIN servicios_adicionales ON clientes.cliente_id = servicios_adicionales.cliente_id
            INNER JOIN tipo_servicios ON clientes_servicios.tipo_servicio = tipo_servicios.servicio_id 
            INNER JOIN colonias ON colonias.colonia_id = clientes_servicios.colonia 
            INNER JOIN mikrotiks ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            INNER JOIN clientes_status ON clientes_servicios.cliente_status = clientes_status.status_id 
            INNER JOIN paquetes ON paquetes.idpaquete = clientes_servicios.cliente_paquete 
            CROSS JOIN status_equipo ON clientes_servicios.status_equipo = status_equipo.status_id 
            INNER JOIN cortes_servicio ON cortes_servicio.corte_id = clientes_servicios.cliente_corte 
            CROSS JOIN modem ON clientes_servicios.modem_instalado = modem.idmodem 
            WHERE  clientes.cliente_id = ?
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }
}