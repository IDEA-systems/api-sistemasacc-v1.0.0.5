<?php

class ReadCustomers extends Messenger
{

    public $count; // Contar el total de clientes por status
    public $status; // Guardar la lista de clientes por status
    public $clientes; // Ontener la lista de clientes
    public $colonias; // Guardar la lista de colonias
    public $cortes; // Guardar la lista de cortes
    public $modems; // Guardar la lista de modems
    public $antenas; // Guardar las antenas
    public $servicio;
    public $servicios_adicionales;
    public $metodos;
    public $paquetes;
    public $tipos;
    public $equipos;
    public $periodos;
    public $stations;
    public $error_message;
    public $is_connect;
    public $details;
    public $ping_status;
    private $cliente_status;
    private $cliente_corte;
    private $corte_inicio;
    private $corte_fin;
    private $search;
    private $colonia;
    private $periodo;
    public $name;
    public $equipos_cliente;
    public $history_payment;
    public $history_negociation;
    public $history_reports;
    private $pendientes;
    private $cliente_id;
    public $mikrotik;
    public $employees;
    public $customer_data;
    public $dates;
    public $payment_notification;
    public $consumption;


    public function __construct($filters = [])
    {
        $this->cliente_corte = isset($filters["cliente_corte"]) ? $filters["cliente_corte"] : null;
        $this->corte_inicio = isset($filters["corte_inicio"]) ? $filters["corte_inicio"] : null;
        $this->corte_fin = isset($filters["corte_fin"]) ? $filters["corte_fin"] : null;
        $this->cliente_status = isset($filters["cliente_status"]) ? $filters["cliente_status"] : null;
        $this->cliente_id = isset($filters["cliente_id"]) ? $filters["cliente_id"] : null;
        $this->colonia = isset($filters["colonia"]) ? $filters["colonia"] : null;
        $this->search = isset($filters["search"]) ? $filters["search"] : null;
    }

    public function get_all_type_customer()
    {
        $SQL = "SELECT * FROM tipos_clientes ORDER BY tipo_cliente_id ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * search_name_address
     * 
     * Buscar si no existe el nombre de la antena
     * @param  mixed $request
     * @return mixed
     */
    public static function search_name_address($request)
    {
        // Search repeat
        $SQL = "SELECT * FROM addresses WHERE name = '$request->name'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return empty($rows);
    }

    /**
     * get_customer_cut_data   Obtener los datos de corte del cliente
     *
     * @return array
     */
    public function get_customer_cut_data()
    {
        $SQL = "SELECT clientes_servicios.cliente_id, clientes_servicios.colonia, cortes_servicio.string, cortes_servicio.dia_pago FROM clientes_servicios INNER JOIN cortes_servicio ON clientes_servicios.cliente_corte = cortes_servicio.corte_id WHERE clientes_servicios.cliente_id = '$this->cliente_id'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
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
     * before_year_periods
     *
     * @return array
     */
    public function before_year_periods()
    {
        $periodo_posterior1 = array(
            "periodo_id" => date("mY", strtotime("- 1 month")),
            "mesinicio" => "Diciembre",
            "mesfin" => "Enero"
        );
        $periodo_posterior2 = array(
            "periodo_id" => date("mY", strtotime("- 2 month")),
            "mesinicio" => "Noviembre",
            "mesfin" => "Diciembre"
        );
        $periodo_posterior3 = array(
            "periodo_id" => date("mY", strtotime("- 3 month")),
            "mesinicio" => "Octubre",
            "mesfin" => "Noviembre"
        );

        $periodos = array($periodo_posterior3, $periodo_posterior2, $periodo_posterior1);
        return $periodos;
    }


    /**
     * get_new_year_periods
     *
     * @return array
     */
    public function get_new_year_periods()
    {
        $periodos = array(
            array(
                "periodo_id" => date("mY", strtotime("+ 1 month")),
                "mesinicio" => "Enero",
                "mesfin" => "Febrero"
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 2 month")),
                "mesinicio" => "Febrero",
                "mesfin" => "Marzo",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 3 month")),
                "mesinicio" => "Marzo",
                "mesfin" => "Abril",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 4 month")),
                "mesinicio" => "Abril",
                "mesfin" => "Mayo",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 5 month")),
                "mesinicio" => "Mayo",
                "mesfin" => "Junio",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 6 month")),
                "mesinicio" => "Junio",
                "mesfin" => "Julio",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 7 month")),
                "mesinicio" => "Julio",
                "mesfin" => "Agosto",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 8 month")),
                "mesinicio" => "Agosto",
                "mesfin" => "Septiembre",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 9 month")),
                "mesinicio" => "Septiembre",
                "mesfin" => "Octubre",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 10 month")),
                "mesinicio" => "Octubre",
                "mesfin" => "Noviembre",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 11 month")),
                "mesinicio" => "Noviembre",
                "mesfin" => "Diciembre",
            ),
            array(
                "periodo_id" => date("mY", strtotime("+ 12 month")),
                "mesinicio" => "Diciembre",
                "mesfin" => "Enero",
            ),
        );

        return $periodos;
    }


    public function get_all_periods($limit = 1)
    {
        $SQL = "SELECT CONCAT(periodo_id, YEAR(CURRENT_DATE())) AS periodo_id, mesinicio, mesfin FROM periodos WHERE NOT EXISTS (SELECT pagos.pago_id FROM pagos WHERE pagos.periodo_id = CONCAT(periodos.periodo_id, YEAR(CURRENT_DATE())) AND pagos.cliente_id = '$this->cliente_id') ORDER BY periodo_id ASC LIMIT $limit";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_customer_periods               Get periods
     * 
     * @param  mixed $cliente_id          {ID} Customer condition
     * @param  mixed $dia_pago            Cutoff date
     * @return array                      Array of poriods
     **/
    public function get_customer_periods($limit)
    {

        $periods = array();

        if (intval(date("m")) == 1) {
            $before_year_periods = $this->before_year_periods();
            foreach ($before_year_periods as $periodos) {
                $periods[] = $periodos;
            }
        }

        $rows = $this->get_all_periods($limit);

        foreach ($rows as $row) {
            $periods[] = $row;
        }

        if (intval(date("m")) == 12) {
            $after_periods = $this->get_new_year_periods();
            foreach ($after_periods as $periodos) {
                $periods[] = $periodos;
            }
        }

        return $periods;
    }

    /**
     * get_promotions_customer                  Obtener las promosiones
     * 
     * @param  mixed $cliente_id
     * @param  mixed $colonia_id
     * @return array
     */
    public function get_promotions_customer()
    {
        $colonia_id = $this->customer_data[0]["colonia"];
        $SQL = " SELECT * FROM promosiones WHERE colonia_id = $colonia_id";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * type_payments          Obtiene los tipos de pago
     *
     * @return array
     */
    public function type_payments()
    {
        $SQL = "SELECT * FROM `pagos_tipos`";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_resources_customer_payment  Obtener los datos de pago del cliente
     *
     * @return void
     */
    public function get_resources_customer_payment($cliente_id, $limit)
    {
        $this->cliente_id = $cliente_id;
        $this->customer_data = $this->get_customer_cut_data();       // Obtener los datos del corte del cliente
        // $this->periodo_actual = $this->get_to_periods();              // Obtener el periodo actual
        $this->periodos = $this->get_customer_periods($limit);             // Obtener los periodos del cliente
        $this->promosiones = $this->get_promotions_customer();       // Obtener las promosiones
        // $this->month_promotions = $this->get_period_promotions();    // Periodos validos para promosiones
        $this->type_payments = $this->type_payments();               // Obtener los tipos de pago
    }

    /**
     * customerFail
     *
     * @param  mixed $cliente_id
     * @return array
     * Get fails by cliente_id
     *
     **/
    public function customerFail()
    {
        $SQL = "SELECT * FROM reportes_fallas WHERE cliente_id = '$this->cliente_id' AND status = 1 AND YEAR(fecha_captura) = YEAR(CURRENT_DATE())";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_all_employs
     *
     * @return array
     * Return list of employs
     * 
     */
    public function get_all_employees()
    {
        $SQL = "SELECT * FROM empleados CROSS JOIN usuarios ON empleados.usuario_id = usuarios.usuario_id WHERE usuarios.usuario_status != 3";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        return $query->fetchAll();
    }


    /**
     * search_address_IPv4
     *
     * Buscar si no se repite la ip de la antena
     * @param  mixed $request
     * @return mixed
     */
    public static function search_address_IPv4($request)
    {
        // Search repeat
        $SQL = "SELECT * FROM addresses WHERE address = '$request->address'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return empty($rows);
    }


    /**
     * Ping
     * Hacer un ping al cliente
     * @param  mixed $cliente_id
     * @return mixed
     */
    public function ping($cliente_id)
    {
        $this->cliente_id = $cliente_id;
        $rows = $this->get_mikrotik_credentials();
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $puerto = $rows[0]["mikrotik_puerto"];

        $conn = new Mikrotik($ip, $user, $password, $puerto);
        if (!$conn->connected) {
            $this->mikrotik["morosos"] = false;
            $this->mikrotik["ping"] = false;
        }

        if (isset($rows[0]["metodo_bloqueo"])) {
            switch ($rows[0]["metodo_bloqueo"]) {
                case "DHCP":
                    $this->mikrotik["morosos"] = $this->customer_is_moroso($rows, $conn);
                    $this->mikrotik["ping"] = $this->mikrotik_exec_ping($conn, $rows);
                    $conn->disconnect();
                break;

                case "ARP":
                    $this->mikrotik["morosos"] = $this->customer_is_moroso($rows, $conn); 
                    $this->mikrotik["ping"] = $this->mikrotik_exec_ping($conn, $rows);
                    $conn->disconnect();
                break;

                case "PPPOE":
                    $this->mikrotik["morosos"] = $this->search_in_secrets($rows, $conn);
                    $this->mikrotik["ping"] = $this->search_in_actives($rows, $conn);
                    $conn->disconnect();
                break;
                default: // Mostrar nada
            }
        }
    }

    public function customer_is_moroso($rows, $conn)
    {
        $cliente_ip = $rows[0]["cliente_ip"];
        $is_morosos = false;
        $list = $conn->comm("/ip/firewall/address-list/getall");
        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]["address"] != $cliente_ip)
                continue;
            if ($list[$i]["address"] == $cliente_ip && $list[$i]["list"] == "MOROSOS") {
                $is_morosos = true;
                break;
            }
        }
        return $is_morosos;
    }


    public function search_in_secrets($rows, $conn)
    {
        $secrets = $conn->comm("/ppp/secret/getall");
        if (!empty($secrets)) {
            foreach ($secrets as $connections) {
                $is_name = $connections['name'] == $rows[0]["user_pppoe"];
                $is_enable = $connections['disabled'] == "false";
                $is_disabled = $connections['disabled'] == "true";
                if ($is_name && $is_enable) {
                    $this->mikrotik['in_secret'] = true;
                    $this->mikrotik['in_morosos'] = false;
                    return;
                }
                else if ($is_name && $is_disabled) {
                    $this->mikrotik['in_secret'] = true;
                    $this->mikrotik['in_morosos'] = true;
                    return;
                }
            }
        }

        $this->mikrotik['in_secret'] = false;
        $this->mikrotik['in_morosos'] = false;
    }


    public function search_in_actives($rows, $conn)
    {
        $actives = $conn->write("/ppp/active/getall");
        
        if (!empty($actives)) {
            foreach ($actives as $connections) {
                if ($connections['name'] == $rows[0]["user_pppoe"]) {
                    $this->mikrotik['in_active'] = true;
                    return;
                }
            }
        }

        $this->mikrotik['in_active'] = false;
    }

    /**
     * mikrotik_exec_ping
     *
     * @param  mixed $conn                Conexion a la API de mikrotik
     * @param  mixed $ip                  IPv4 a la que se ara el ping
     * @param  mixed $mac                 MAC buscar en la lista de bloqueados
     * @param  mixed $metodo_bloqueo      Metodo de administracion del cliente
     * @return mixed                       Retorna el status del ping true/false seguido del mensaje de error
     */
    public function mikrotik_exec_ping($conn, $rows)
    {
        $ip = $rows[0]["cliente_ip"];
        $results = $conn->comm("/ping", [
            "address" => $ip,
            "count" => 3,
            "interval" => 1
       ]);
        $firts = $results[0]["packet-loss"] == 0;
        $second = $results[1]["packet-loss"] == 0;
        $three = $results[2]["packet-loss"] == 0;
        if ($firts && $second && $three) return true;
        if (!$firts || !$second || !$three) return false;
        return false;
    }


        
    /**
     * get_mikrotik_credentials
     *
     * @return array
     */
    public function get_mikrotik_credentials()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.metodo_bloqueo, 
                clientes.cliente_ip, 
                clientes.cliente_nombres, 
                clientes.cliente_apellidos, 
                clientes.cliente_mac, 
                clientes.cliente_email, 
                clientes.interface_arp, 
                clientes.profile,
                clientes.user_pppoe,
                clientes.password_pppoe,
                clientes_servicios.cliente_paquete, 
                clientes_servicios.tipo_servicio, 
                clientes_servicios.suspender, 
                clientes.serie_modem, 
                clientes_servicios.colonia, 
                colonias.mikrotik_control, 
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
     * get_consunption_customer       Obtener el consumo del cliente
     *
     * @param  mixed $request
     * @return mixed
     */
    public function get_consunption_customer($request)
    {
        $this->cliente_id = $request->cliente_id;
        $rows = $this->get_mikrotik_credentials();
        if (isset($rows[0]["metodo_bloqueo"])) {
            switch ($rows[0]["metodo_bloqueo"]) {
                case "DHCP":
                    $this->get_consunption_dhcp($rows);
                break;
                case "ARP":
                    $this->get_consunption_arp($rows);
                break;
                case "PPPOE":
                    $this->get_consunption_pppoe($rows);
                break;
                default: // Mostrar nada
            }
        }
    }

    /**
     * get_consunption_dhcp
     *
     * @param  array $rows
     * @return mixed
     */
    public function get_consunption_dhcp($rows)
    {
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $puerto = $rows[0]["mikrotik_puerto"];
        $conn = new Mikrotik($ip, $user, $password, $puerto);
        $data = $conn->get_consunption_address($rows[0]["cliente_ip"]);
        $this->consumption = $data;
        $conn->disconnect();
    }


    public function get_consunption_arp($rows)
    {
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $puerto = $rows[0]["mikrotik_puerto"];
        $conn = new Mikrotik($ip, $user, $password, $puerto);
        $data = $conn->get_consunption_address($rows[0]["cliente_ip"]);
        $this->consumption = $data;
        $conn->disconnect();
    }


    public function get_consunption_pppoe($rows)
    {
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $puerto = $rows[0]["mikrotik_puerto"];
        $conn = new Mikrotik($ip, $user, $password, $puerto);
        $data = $conn->get_consunption_address($rows[0]["cliente_ip"]);
        $this->consumption = $data;
        $conn->disconnect();
    }


    public function history($cliente_id)
    {
        $this->history_payment = $this->history_payment($cliente_id);
        $this->history_negociation = $this->history_negociation($cliente_id);
        $this->history_reports = $this->history_reports($cliente_id);
        return true;
    }

    
    /**
     * history_payment
     *
     * @param  string $cliente_id
     * @return array
     */
    public function history_payment($cliente_id) : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres,
                CONCAT(empleados.empleado_nombre, ' ', empleados.empleado_apellido) AS empleado,
                pagos.* 
            FROM pagos 
            INNER JOIN clientes 
            ON clientes.cliente_id = pagos.cliente_id 
            INNER JOIN usuarios 
            ON pagos.usuario_captura = usuarios.usuario_id 
            LEFT JOIN empleados 
            ON usuarios.usuario_id = empleados.usuario_id 
            WHERE pagos.cliente_id = ? 
            ORDER BY pagos.status_pago DESC
        ");
        $query->execute([ $cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function history_negociation($cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM negociaciones 
            INNER JOIN clientes 
            ON negociaciones.cliente_id = clientes.cliente_id 
            LEFT JOIN empleados 
            ON empleados.usuario_id = negociaciones.usuario_negociacion 
            WHERE negociaciones.cliente_id = ? 
            ORDER BY negociaciones.status_negociacion DESC
        ");
        $query->execute([ $cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function history_reports($cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, 
                reportes_fallas.*,
                empleados.*
            FROM clientes
            INNER JOIN reportes_fallas 
            ON clientes.cliente_id = reportes_fallas.cliente_id 
            INNER JOIN empleados
            ON reportes_fallas.empleado_control = empleados.empleado_id
            WHERE reportes_fallas.cliente_id = ?
        ");
        $query->execute([ $cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function transform_array_string_in_sql($periodo_id)
    {
        $text = json_encode($periodo_id);
        $order = array('"', '[',']');
        $replace = array("'", "(",")");
        $in = str_replace($order, $replace, $text);
        return $in;
    }

    public function get_after_period()
    {
        $periodos = array();
        $fecha = date("mY", strtotime("+ 1 month"));
        array_push($periodos, $fecha);
        return $periodos;
    }

    /**
     * Clientes
     * Obtener la lista de clientes
     * @return array
     **/
    public function clientes()
    {
        $SQL = " 
            SELECT clientes.*, clientes_servicios.*, cortes_servicio.*, CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres,  tipo_servicios.nombre_servicio, colonias.nombre_colonia, colonias.mikrotik_control, mikrotiks.mikrotik_nombre, paquetes.nombre_paquete, modem.modelo as modem, clientes_status.status_id, clientes_status.nombre_status FROM clientes 
            INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id 
            LEFT JOIN servicios_adicionales ON clientes.cliente_id = servicios_adicionales.cliente_id
            INNER JOIN tipo_servicios ON clientes_servicios.tipo_servicio = tipo_servicios.servicio_id 
            INNER JOIN colonias ON colonias.colonia_id = clientes_servicios.colonia 
            INNER JOIN mikrotiks ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            INNER JOIN clientes_status ON clientes_servicios.cliente_status = clientes_status.status_id 
            INNER JOIN paquetes ON paquetes.idpaquete = clientes_servicios.cliente_paquete 
            CROSS JOIN status_equipo ON clientes_servicios.status_equipo = status_equipo.status_id 
            INNER JOIN cortes_servicio ON cortes_servicio.corte_id = clientes_servicios.cliente_corte 
            LEFT JOIN modem ON clientes_servicios.modem_instalado = modem.idmodem WHERE 
        ";

        if (isset($this->cliente_corte)) {
            $periods = $this->get_to_periods();
            $IN_PERIODS = $this->transform_array_string_in_sql($periods);
            $SQL .= "clientes_servicios.cliente_status != 0 AND clientes_servicios.cliente_corte BETWEEN '$this->corte_inicio' AND '$this->corte_fin' AND NOT EXISTS ( SELECT pagos.pago_id FROM pagos WHERE pagos.cliente_id = clientes.cliente_id AND pagos.periodo_id IN $IN_PERIODS AND pagos.status_pago != 0)
            AND clientes_servicios.cliente_status != 3 ORDER BY clientes_servicios.cliente_corte ASC LIMIT 1000";
        }

        if (isset($this->cliente_status)) {
            $SQL .= "clientes_servicios.cliente_status != 0 AND clientes_servicios.cliente_status = $this->cliente_status ORDER BY clientes_servicios.cliente_status ASC LIMIT 1000 ";
        }

        if (isset($this->search)) {
            $SQL .= "CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) LIKE '%$this->search%' OR clientes.cliente_nombres LIKE '%$this->search%' OR clientes.cliente_apellidos LIKE '%$this->search%' OR clientes.cliente_ip LIKE '%$this->search' OR clientes.cliente_ap LIKE '%$this->search' OR clientes.cliente_mac LIKE '%$this->search%' OR clientes.serie_modem LIKE '%$this->search%' OR tipo_servicios.nombre_servicio LIKE '%$this->search%' OR colonias.nombre_colonia LIKE '%$this->search%' OR clientes.cliente_telefono LIKE '%$this->search' OR clientes.cliente_email LIKE '%$this->search%' AND clientes_servicios.cliente_status != 0 ORDER BY clientes_servicios.cliente_status ASC LIMIT 1000";
        }

        if (isset($this->colonia)) {
            $SQL .= "clientes_servicios.cliente_status != 0 AND clientes_servicios.colonia = $this->colonia ORDER BY clientes_servicios.cliente_status ASC LIMIT 1000 ";
        }

        if (
            !isset($this->cliente_corte) &&
            !isset($this->cliente_status) &&
            !isset($this->search) &&
            !isset($this->colonia) &&
            !isset($this->cliente_id)
        ) {
            $SQL .= "clientes_servicios.cliente_status != 0 AND clientes_servicios.cliente_status != 3 ORDER BY clientes_servicios.cliente_status ASC LIMIT 1000";
        }

        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }
    
    
        
    /**
     * get_by_id
     *
     * @param  mixed $cliente_id
     * @return array
     */
    public function get_by_id($cliente_id)
    {
        $SQL = " 
            SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, clientes.*, clientes_servicios.*, servicios_adicionales.id, servicios_adicionales.servicio_id, servicios_adicionales.costo_servicio, servicios_adicionales.equipo_id, servicios_adicionales.serie_equipo, servicios_adicionales.equipo_status, servicios_adicionales.status_servicio, tipo_servicios.nombre_servicio, colonias.nombre_colonia, colonias.mikrotik_control, mikrotiks.mikrotik_nombre, paquetes.nombre_paquete, modem.modelo as modem, clientes_status.status_id, clientes_status.nombre_status FROM clientes 
            INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id 
            LEFT JOIN servicios_adicionales ON clientes.cliente_id = servicios_adicionales.cliente_id
            INNER JOIN tipo_servicios ON clientes_servicios.tipo_servicio = tipo_servicios.servicio_id 
            INNER JOIN colonias ON colonias.colonia_id = clientes_servicios.colonia 
            INNER JOIN mikrotiks ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            INNER JOIN clientes_status ON clientes_servicios.cliente_status = clientes_status.status_id 
            INNER JOIN paquetes ON paquetes.idpaquete = clientes_servicios.cliente_paquete 
            CROSS JOIN status_equipo ON clientes_servicios.status_equipo = status_equipo.status_id 
            INNER JOIN cortes_servicio ON cortes_servicio.corte_id = clientes_servicios.cliente_corte 
            CROSS JOIN modem ON clientes_servicios.modem_instalado = modem.idmodem WHERE clientes.cliente_id = ?
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     *
     * @param  mixed $cliente_email
     * @return bool
     **/
    public function search_email($cliente_email, $cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) 
                AS nombres,
                clientes.cliente_email
            FROM `clientes` 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            WHERE clientes.cliente_email = ? 
            AND clientes.cliente_id != ? 
            AND clientes_servicios.cliente_status 
            IN (1,2,4,5,6)
        ");
        $query->execute([
            $cliente_email,
            $cliente_id
       ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * 
     * Telefono
     * @param  mixed $cliente_telefono
     * @return bool
     **/
    public function search_phone($cliente_telefono, $cliente_id = '')
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres,
                clientes.cliente_telefono 
            FROM `clientes` 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            WHERE clientes.cliente_telefono = ? 
            AND clientes.cliente_id != ? 
            AND clientes_servicios.cliente_status 
            IN (1,2,4,5,6)
        ");
        $query->execute([
            $cliente_telefono,
            $cliente_id
       ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * MAC
     * Busca la mac ingresada
     * @param  mixed $cliente_mac
     * @return bool
     **/
    public function searchAddressMAC($cliente_id = '', $cliente_mac)
    {
        $query = Flight::gnconn()->prepare("
            SELECT CONCAT(
                clientes.cliente_nombres, ' ', 
                clientes.cliente_apellidos
            ) AS nombres, clientes.cliente_mac
            FROM `clientes` 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            WHERE clientes.cliente_mac = ? 
            AND clientes.cliente_id != ? 
            AND clientes_servicios.cliente_status 
            IN (1,2,4,5,6)
        ");
        $query->execute([
            $cliente_mac,
            $cliente_id
       ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * IPv4
     * Validar que no se repita la ip
     * @param  mixed $cliente_ip
     * @param  mixed $cliente_id
     * @return bool
     **/
    public function searchAddressIPv4($cliente_id = '', $cliente_ip)
    {
        $query = Flight::gnconn()->prepare("
            SELECT CONCAT(
                clientes.cliente_nombres, ' ', 
                clientes.cliente_apellidos
            ) AS nombres, clientes.cliente_ip
            FROM `clientes` 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            WHERE clientes.cliente_ip = ? 
            AND clientes.cliente_id != ?
            AND clientes_servicios.cliente_status 
            IN (1,2,4,5,6)
        ");
        $query->execute([
            $cliente_ip,
            $cliente_id
       ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * Serie
     * Validar que no se repita el numero de serie
     * @param  mixed $cliente_serie
     * @param  mixed $cliente_id
     * @return bool
     * 
     **/
    public function search_serie_modem($serie_modem, $cliente_id)
    {
        // $id = !is_null($cliente_id) ? $cliente_id : '';
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres,
                clientes.serie_modem
            FROM `clientes` 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            WHERE clientes.serie_modem = ? 
            AND clientes.cliente_id != ? 
            AND clientes_servicios.cliente_status 
            IN (1,2,4,5,6)
        ");
        $query->execute([
            $serie_modem,
            $cliente_id
       ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * Verificar existencia en mikrotik
     * 
     * Busca los datos del cliente en el mikrotik segun su metodo de bloqueo
     * 
     * @param  string $cliente_id
     * 
     * @return void
     */
    public function checkout_mikrotik($cliente_id)
    {
        $this->cliente_id = $cliente_id;
        $rows = $this->get_mikrotik_credentials();
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $port = $rows[0]["mikrotik_puerto"];
        $password = $rows[0]["mikrotik_password"];
        $conn = new Mikrotik($ip, $user, $password, $port);
        if (isset($rows[0]["metodo_bloqueo"])) {
            switch ($rows[0]["metodo_bloqueo"]) {
                case "DHCP":
                    $this->search_in_method_dhcp($rows, $conn);
                    $conn->disconnect();
                break;
                case "ARP":
                    $this->search_in_method_arp($rows, $conn);
                    $conn->disconnect();
                break;
                case "PPPOE":
                    $this->search_in_method_pppoe($rows, $conn);
                    $conn->disconnect();
                break;
                default: // Hacer nada
            }
        }
    }

    public function search_in_method_dhcp($rows, $conn)
    {
        $this->search_in_leases($conn, $rows);
        $this->search_in_queue($conn, $rows);
        $this->search_in_firewall($conn, $rows);
        $this->search_in_morosos($conn, $rows);
    }

    public function search_in_method_arp($rows, $conn)
    {
        $this->search_in_arp($conn, $rows);
        $this->search_in_queue($conn, $rows);
        $this->search_in_firewall($conn, $rows);
        $this->search_in_morosos($conn, $rows);
    }

    public function search_in_method_pppoe($rows, $conn)
    {
        $this->search_in_secrets($conn, $rows);
        $this->search_in_actives($conn, $rows);
    }

    /**
     * Buscar el leases
     *
     * Buscar la ip del cliente en la lista de leases
     * 
     * @param  mixed $conn      Conexion al mirkotik
     * @param  array $rows        Datos del cliente
     * @return void            Retorna true o false
     */
    public function search_in_leases($conn, $rows)
    {
        $cliente_ip = $rows[0]["cliente_ip"];
        $leases = $conn->comm("/ip/dhcp-server/lease/getall");
        if (!empty($leases)) {
            for ($i = 0; $i < count($leases); $i++) {
                if ($leases[$i]["address"] == $cliente_ip) {
                    $this->mikrotik["in_leases"] = true;
                    $this->mikrotik["leases"] = $leases[$i];
                    return;
                }
            }
        }
        $this->mikrotik["in_leases"] = false;
    }


    public function search_in_arp($conn, $rows)
    {
        $cliente_ip = $rows[0]["cliente_ip"];
        $leases = $conn->comm("/ip/arp/getall");

        if (!empty($leases)) {
            for ($i = 0; $i < count($leases); $i++) {
                if ($leases[$i]["address"] == $cliente_ip) {
                    $this->mikrotik["in_arp"] = true;
                    $this->mikrotik["arp"] = $leases[$i];
                    return;
                }
            }
        }
        
        $this->mikrotik["in_arp"] = false;
    }

    /**
     * search_in_queue
     *
     * @param  mixed $conn
     * @param  mixed $rows
     * @return void
     */
    public function search_in_queue($conn, $rows)
    {
        $cliente_ip = $rows[0]["cliente_ip"];
        $list = $conn->comm("/queue/simple/getall");
        
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                $target = isset($list[$i]["target"]) ? explode("/", $list[$i]["target"])[0] : "";
                if ($target == $cliente_ip) {
                    $this->mikrotik["in_queues"] = true;
                    $this->mikrotik["queues"] = $list[$i];
                    return;
                }
            }
        }
        
        $this->mikrotik["in_queues"] = false;
    }

    /**
     * search_in_firewall
     *
     * @param  mixed $conn
     * @param  mixed $rows
     * @return void
     */
    public function search_in_firewall($conn, $rows)
    {
        $cliente_ip = $rows[0]["cliente_ip"];
        $list = $conn->comm("/ip/firewall/address-list/getall");
        
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["address"] == $cliente_ip && $list[$i]["list"] == "ACTIVE") {
                    $this->mikrotik["in_active"] = true;
                    $this->mikrotik["active"] = $list[$i];
                    return;
                }
            }
        }

        $this->mikrotik["in_active"] = false;
    }

    /**
     * search_in_morosos
     *
     * @param  mixed $conn
     * @param  mixed $rows
     * @return void
     */
    public function search_in_morosos($conn, $rows)
    {
        $cliente_ip = $rows[0]["cliente_ip"];
        $list = $conn->comm("/ip/firewall/address-list/getall");
        
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["address"] == $cliente_ip && $list[$i]["list"] == "MOROSOS") {
                    $this->mikrotik["in_morosos"] = true;
                    $this->mikrotik["list_morosos"] = $list[$i];
                    return;
                }
            }
        }
        
        $this->mikrotik["in_morosos"] = false;
    }
}