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
    public $numero_negociacion;
    public $cliente_status;

    public function __construct($request = [])
    {
        $this->fecha_inicio = isset($request->fecha_inicio) ? $request->fecha_inicio : date('Y-m-d');
        $this->fecha_captura = isset($request->fecha_captura) ? $request->fecha_captura : date('Y-m-d:h:m:s');
        $this->status_negociacion = isset($request->status_negociacion) ? $request->status_negociacion : 1;
        $this->numero_negociacion = isset($request->numero_negociacion) ? $request->numero_negociacion : 1;
        $this->id_negociacion = isset($request->id_negociacion) ? $request->id_negociacion : null;
        $this->usuario_negociacion = isset($request->usuario_id) ? $request->usuario_id : '';
        $this->cliente_id = isset($request->cliente_id) ? $request->cliente_id : null;
        $this->comentario = isset($request->comentario) ? $request->comentario : null;
        $this->fecha_fin = isset($request->fecha_fin) ? $request->fecha_fin : null;
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


    public function get_all_negociations($filters)
    {
        $limit = $filters['limit'];
        $status = $filters['status'];
        $empleado_id = $filters['empleado_id'];
        $fecha_inicio = $filters['fecha_inicio'];
        $fecha_fin = $filters['fecha_fin'];
        $parameters = $filters['parameters'];

        $sql = "
            SELECT 
                negociaciones.*,
                status_negociaciones.nombre as nombre_status_negociacion,
                clientes.cliente_nombres,
                clientes.cliente_apellidos,
                colonias.nombre_colonia,
                empleados.empleado_nombre,
                empleados.empleado_apellido
            FROM negociaciones 
            INNER JOIN status_negociaciones
            ON negociaciones.status_negociacion = status_negociaciones.status_negociacion
            INNER JOIN usuarios 
            ON negociaciones.usuario_negociacion = usuarios.usuario_id
            LEFT JOIN empleados 
            ON usuarios.usuario_id = empleados.usuario_id
            INNER JOIN clientes
            ON negociaciones.cliente_id = clientes.cliente_id
            INNER JOIN clientes_servicios 
            ON negociaciones.cliente_id = clientes_servicios.cliente_id
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id
            WHERE negociaciones.status_negociacion $status
        ";

        if (!is_null($empleado_id)) {
            $sql .= " AND empleados.empleado_id = '$empleado_id' ";
        }

        if (is_null($fecha_fin) && !is_null($fecha_inicio)) {
            $sql .= " AND negociaciones.fecha_inicio = '$fecha_inicio' ";
        }

        if (is_null($fecha_inicio) && !is_null($fecha_fin)) {
            $sql .= " AND negociaciones.fecha_fin = '$fecha_fin' ";
        }

        if (!is_null($fecha_fin) && !is_null($fecha_fin)) {
            $sql .= " 
                AND negociaciones.fecha_inicio >= '$fecha_inicio' 
                AND negociaciones.fecha_inicio <= '$fecha_fin'
                OR negociaciones.fecha_fin >= '$fecha_inicio' 
                AND negociaciones.fecha_fin <= '$fecha_fin'
            ";
        }

        if (!is_null($parameters)) {
            $sql .= " 
                AND clientes.cliente_nombres LIKE '%$parameters%' 
                OR clientes.cliente_apellidos LIKE '%$parameters%'
            ";
        }

        $sql .= " ORDER BY negociaciones.status_negociacion ASC $limit";
        $query = Flight::gnconn()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_negociation_to_month()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM negociaciones 
            WHERE cliente_id = ? 
            AND YEAR(CURRENT_DATE()) = YEAR(fecha_captura)
            AND MONTH(fecha_captura) = MONTH(CURRENT_DATE()) 
            ORDER BY fecha_fin ASC
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
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


    public function date_diff()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
            DATEDIFF(DATE(?), DATE(?)) 
            AS dias
        ");
        $query->execute([ 
            $this->fecha_fin,
            $this->fecha_inicio, 
        ]);
        $rows = $query->fetchAll();
        return $rows;
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

    /**
     * Summary of negociation_in_customer_active
     * 
     * Establece una negociacion a los clientes activos 
     * Y calcula el status el numero de negociacion la diferencia de dias entre las fechas
     * Asi como tambien valida si ya existen negociaciones pendientes o corriendo para evitar
     * agregar negociaciones duplicadas o con errores de fechas
     * 
     * @param mixed $customer
     * @return mixed
     */
    public function negociation_in_customer_active($customer)
    {
        $datediff = $this->date_diff();

        /**
         * 
         * La fecha de inicio y de fin de la negociacion 
         * no puede ser mas de 31 dias y no puede ser menor a 8 dias
         * 
        **/
        if ($datediff[0]["dias"] > 31 || $datediff[0]["dias"] < 8) {
            $this->error = true;
            $this->error_message = "No se adminten negociaciones mayores a 31 dias y tampoco menores a 8 dias!";
            return false;
        }

        $mensuales = $this->get_negociation_to_month();

        /**
         * 
         * Si el cliente ya tiene mas de 3 negociaciones 
         * No aplica para la negociacion
         * 
        **/
        if (count($mensuales) > 3) {
            $this->error = true;
            $this->error_message = "El cliente ya tiene una negociacion finalizada!";
            return false;
        }

        /**
         * 
         * Si el cliente ya tiene mas de 3 negociaciones 
         * O las negociaciones suman mas de 3 modificaciones
         * No aplica para la negociacion
         * 
        **/
        if (count($mensuales) > 0) {
            $count_negociations = 0;
            foreach ($mensuales as $negociacion) {
                $count_negociations += $negociacion["numero_negociacion"];
            }

            if ($count_negociations >= 3) {
                $this->error = true;
                $this->error_message = "El cliente ya no es apto para otra negociacion!";
                return false;
            }
        }

        $pendientes = $this->get_pending_or_runing();

        /**
         * 
         * Si existen negociaciones pendientes o corriendo 
         * y las numero_negociacion son mayores a 4 entonces no se puede 
         * extender mas la negociacion y se envia el mensaje de error
         * 
        **/
        if (count($pendientes) >= 1) {

            // Ultima negociacion
            $key = count($pendientes) - 1;

            // ¿Se puede agregar una negociacion mas?
            $is_changeable = $pendientes[$key]["numero_negociacion"] <= 2;

            /**
             * 
             * La negociacion no puede extenderse mas de 3 veces
             * ya que las numero_negociacion de la negociacion 
             * corresponden a otra negociacion
             * 
            */
            if (!$is_changeable) {
                $this->error = true;
                $this->error_message = "No se puede agregar mas negociaciones!";
                return false;
            }

            
            $nueva_fecha_fin = new DateTime($this->fecha_fin);
            $anterior_fecha_fin = new DateTime($pendientes[$key]["fecha_fin"]);
            $this->fecha_inicio = $pendientes[$key]["fecha_inicio"];

            /**
             * 
             * Las fecha_fin de la negociacion no puede cruzarse 
             * con la fecha fin de la negociacion que esta pendiente o corriendo
             * 
            **/
            if ($nueva_fecha_fin < $anterior_fecha_fin) {
                $this->error = true;
                $this->error_message = "Ya existe una negociacion pendiente que termina despues de la fecha que intenta agregar!";
                return false;
            }

            // Esta en la misma fecha de inicio
            $is_date_iguality = $this->fecha_inicio == $pendientes[$key]["fecha_inicio"];
            $is_pending = $pendientes[$key]["status_negociacion"] == 2;
            $is_running = $pendientes[$key]["status_negociacion"] == 1;

            /**
             * 
             * Si los datos son los mismos y la negociacion aun no comienza
             * entonces dejarlo como esta sin contar las modificaciones
             * 
            */
            if ($nueva_fecha_fin == $anterior_fecha_fin && $is_date_iguality && $is_pending) {
                $this->activacion = true;
                $this->whatsapp = true;
                return true;
            }

            if ($nueva_fecha_fin == $anterior_fecha_fin && $is_date_iguality && $is_running) {
                $this->activacion = true;
                $this->whatsapp = true;
                return true;
            }

            $this->cliente_status = $pendientes[$key]["status_negociacion"] == 2 ? $customer[0]["cliente_status"] : 4;
            $this->numero_negociacion = $pendientes[$key]["numero_negociacion"] + 1;
            $this->status_negociacion = $pendientes[$key]["status_negociacion"];
            $this->id_negociacion = $pendientes[$key]["id_negociacion"];

            /**
             * 
             * Actualiza la negociacion 
             * con los datos de la negociacion actual
             * 
            **/
            $this->trevel_negociation();
            if ($this->error) return false;

            /**
             * 
             * Enviar el mensaje de la negociacion
             * Nuevamente con los datos actualizados
            **/
            $this->send_negociation_whatsapp();

            /**
             * Activa el servicio en el mikrotik
             * de nuestro cliente en cuestion
            **/
            $mikrotik = $this->get_mikrotik_credentials();
            $this->activate_service($mikrotik);
        }

        if (count($pendientes) == 0) {
            $current_day = date('d');
            $cliente_corte = $customer[0]["cliente_corte"];

            /**
             * 
             * Si el dia actual es mayor o igual al dia de corte del cliente
             * se cambia el status del cliente a 4 (negociacion)
             * de lo contrario se mantiene el status del cliente
             * 
            **/
            if ($current_day >= $cliente_corte) {
                $this->cliente_status = 4;
                $this->status_negociacion = 1;
            } 

            if ($current_day < $cliente_corte) {
                $this->cliente_status = $customer[0]["cliente_status"];
                $this->status_negociacion = 2;
            }

            /**
             * 
             * Insertar la nueva negociacion
             * 
            **/
            $this->insert_new_negociation();
            if ($this->error) return false;

            /**
             * Enviar el mensaje de la negociacion
            **/
            $this->send_negociation_whatsapp();

            /**
             * 
             * Activar el servicio de nuestro cliente
             * en el mikrotik routerboard
             * 
            **/
            $mikrotik = $this->get_mikrotik_credentials();
            $this->activate_service($mikrotik);
        }
    }


    public function negociation_in_customer_suspended($customer)
    {
        $datediff = $this->date_diff();

        /**
         * 
         * La fecha de inicio y de fin de la negociacion 
         * no puede ser mas de 31 dias y no puede ser menor a 8 dias
         * 
        **/
        if ($datediff[0]["dias"] > 31 || $datediff[0]["dias"] < 8) {
            $this->error = true;
            $this->error_message = "No se adminten negociaciones mayores a 31 dias y tampoco menores a 8 dias!";
            return false;
        }

        $negociations = $this->get_negociation_to_month();

        /**
         * 
         * Si el cliente ya tiene mas de 3 negociaciones 
         * no se puede agregar una nueva negociacion
         * 
        **/
        if (count($negociations) > 3) {
            $this->error = true;
            $this->error_message = "El cliente ya no es apto para otra negociacion!";
            return false;
        }

        $count_negociations = 0;

        /**
         * 
         * Si solo tiene 1,2 negociaciones las cuales al sumar sus numero_negociacion 
         * no puede ser mayor a 3, entonces se puede agregar una nueva negociacion
         * 
        **/
        if (count($negociations) <= 3) {
            foreach ($negociations as $negociation) {
                $count_negociations += $negociation["numero_negociacion"];
            }
        }

        /**
         * 
         * Revisamos si las sumas de las negociaciones establecidas en el mes
         * No superan el numero 3 ya que es el numero maximo de negociaciones o de modificaciones 
         * Que puede tener un cliente en un mes, hablando de negociaciones
         * 
        **/
        if ($count_negociations >= 3) {
            $this->error = true;
            $this->error_message = "El cliente ya no es apto para otra negociacion!";
            return false;
        }

        $this->numero_negociacion = $count_negociations + 1;
        $this->fecha_inicio = date('Y-m-d');
        $this->cliente_status = 4;

        /**
         * 
         * Insertar la nueva negociacion
         * 
        **/
        $this->insert_new_negociation();
        if ($this->error) return false;

        /**
         * 
         * Enviar el mensaje de la negociacion
         * 
        **/
        $this->send_negociation_whatsapp();

        /**
         * 
         * Activar el servicio de nuestro cliente
         * en el mikrotik routerboard
         * 
        **/
        $mikrotik = $this->get_mikrotik_credentials();
        $this->activate_service($mikrotik);
    }


    /**
     * add_negociation_as_root
     * 
     * Agrega una negociación como root
     * 
     * Esta función se encarga de agregar una nueva negociación para un cliente, 
     * verificando previamente si el cliente tiene pagos pendientes para el período de la negociación.
     * Si el cliente no tiene pagos pendientes, se procede a realizar las operaciones según el estado del cliente.
     * 
     * @return void
    **/
    public function add_negociation_as_root()
    {
        $explode_date = explode('-', $this->fecha_inicio);
        $periodo_id = $explode_date[1].$explode_date[0];

        /**
         * Buscar los pagos del periodo que corresponden a la negociacion
         */
        $payments = $this->payment_from_period_negociation($periodo_id);

        /**
         * Si tiene pagos de ese periodo la negociacion no se puede realizar
         */
        if (count($payments) > 0) {
            $this->error = true;
            $this->error_message = "Ya existen pagos para el periodo seleccionado!";
            return;
        }

        $customer = $this->get_customer_by_id();

        /**
         * Realizar las operaciones 
         * segun el status del cliente
        **/
        if (isset($customer[0]["cliente_status"])) {
            switch ($customer[0]["cliente_status"]) {
                case 1:
                    $this->negociation_in_customer_active($customer);
                break;
                case 2:
                    $this->negociation_in_customer_suspended($customer);
                break;
                case 3:
                    $this->error = true;
                    $this->error_message = "El cliente debe estar activo para agregar una negociacion!";
                break;
                case 4:
                    $this->negociation_in_customer_active($customer);
                break;
                case 5:
                    $this->negociation_in_customer_active($customer);
                break;
                case 6:
                    $this->negociation_in_customer_active($customer);
                break;
                default:
                    $this->error = true;
                    $this->error_message = "El cliente debe estar activo para agregar una negociacion!";
            }
        }

    }


    public function add_negociation_as_user_normal()
    {
        $explode_date = explode('-', $this->fecha_inicio);
        $periodo_id = $explode_date[0].$explode_date[1];

        /**
         * Buscar los pagos del periodo que corresponden a la negociacion
         */
        $payments = $this->payment_from_period_negociation($periodo_id);

        /**
         * Si tiene pagos de ese periodo la negociacion no se puede realizar
         */
        if (count($payments) > 0) {
            $this->error = true;
            $this->error_message = "El cliente cuenta con pagos para el periodo de la negociacion!";
            return;
        }

        /**
         * Obtener las negociaciones mensuales
        **/
        $customer = $this->get_customer_by_id();

        // Status no permitidos
        $is_disabled = in_array($customer[0]["cliente_status"], [3,4]);

        // Solo se puede agregar a clientes autorizados
        if ($is_disabled) {
            $this->error = true;
            $this->error_message = "No puede agregar la negociacion!";
            return false;
        }

        $mensuales = $this->get_negociation_to_month();

        /**
         * 
         * Si el cliente ya tiene mas de 3 negociaciones 
         * No aplica para la negociacion
         * 
        **/
        if (count($mensuales) > 3) {
            $this->error = true;
            $this->error_message = "El cliente ya tiene una negociacion finalizada!";
            return false;
        }

        /**
         * 
         * Si el cliente ya tiene mas de 3 negociaciones 
         * O las negociaciones suman mas de 3 modificaciones
         * No aplica para la negociacion
         * 
        **/
        if (count($mensuales) > 0) {
            $count_negociations = 0;
            foreach ($mensuales as $negociacion) {
                $count_negociations += $negociacion["numero_negociacion"];
            }

            if ($count_negociations >= 3) {
                $this->error = true;
                $this->error_message = "El cliente ya no es apto para otra negociacion!";
                return false;
            }
        }

        $datediff = $this->date_diff();

        /**
         * 
         * Los dias de diferencia entre las fechas 
         * no deben ser manores a 8 o mayores a 31 dias
         * 
        **/
        if ($datediff[0]['dias'] > 31 || $datediff[0]["dias"] < 8) {
            $this->error = true;
            $this->error_message = "La fecha de finalizacion no puede ser menor a la fecha de inicio!";
            return false;
        }

        /**
         * 
         * Si existen negociaciones pendientes o corriendo
         * El usuario normal no puede agregar una nueva negociacion
         * 
        **/
        $pendientes = $this->get_pending_or_runing();

        if (count($pendientes) > 0) {
            $this->error = true;
            $this->error_message = "El cliente ya tiene una negociacion pendiente o corriendo!";
            return false;
        }

        switch($customer[0]["cliente_status"]) {
            case 1:
                $this->negociation_in_customer_active($customer);
            break;
            case 2:
                $this->negociation_in_customer_suspended($customer);
            break;
            case 3:
                $this->error = true;
                $this->error_message = "El cliente debe estar activo para agregar una negociacion!";
            break;
            case 4:
                $this->negociation_in_customer_active($customer);
            break;
            case 5:
                $this->negociation_in_customer_active($customer);
            break;
            case 6:
                $this->negociation_in_customer_active($customer);
            break;
            default:
                $this->error = true;
                $this->error_message = "El cliente debe estar activo para agregar una negociacion!";
        }
    }



    public function payment_from_period_negociation($periodo_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM pagos 
            WHERE cliente_id = ? 
            AND pagos.periodo_id = ?
            AND status_pago IN(1,2)
            ORDER BY pago_fecha_captura ASC
        ");
        $query->execute([ 
            $this->cliente_id,
            $periodo_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_pending_or_runing()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM negociaciones 
            WHERE cliente_id = ? 
            AND status_negociacion IN(1,2)
            ORDER BY fecha_captura ASC
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * 
     * Finaliza las negociaciones activas para un cliente específico
     *
     * Esta función actualiza el estado de las negociaciones a finalizado (status_negociacion = 3)
     * para un cliente dado, cuando la fecha de finalización de la negociación es la fecha actual.
     *
     * @param string $cliente_id El ID del cliente cuyas negociaciones se finalizarán
     *
     * @return void
     *
     * @throws Exception Si ocurre un error durante la ejecución de la consulta SQL
     * 
    **/
    public function end_negociations($cliente_id)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE negociaciones 
                SET status_negociacion = 3,
                fecha_fin = CURRENT_DATE()
                WHERE cliente_id = ?
            ");
            $query->execute([ $cliente_id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al intentar agregar la negociacion!";//$error->getMessage();
        }
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


    public function trevel_negociation()
    {
        $query = Flight::gnconn()->prepare("
            UPDATE negociaciones 
            JOIN clientes_servicios 
            ON negociaciones.cliente_id = clientes_servicios.cliente_id
            SET negociaciones.status_negociacion = ?,
            negociaciones.fecha_fin = ?,
            negociaciones.numero_negociacion = ?,
            negociaciones.comentarios = CONCAT(negociaciones.comentarios, '\nComentario $this->numero_negociacion: ', ?),
            clientes_servicios.cliente_status = ?
            WHERE negociaciones.cliente_id = ?
            AND negociaciones.id_negociacion = ?
        ");
        $query->execute([
            $this->status_negociacion,
            $this->fecha_fin,
            $this->numero_negociacion,
            $this->comentario,
            $this->cliente_status,
            $this->cliente_id,
            $this->id_negociacion
        ]);
    }

    
    /**
     * insert_new_negociation
     *
     * @return void
     */
    public function insert_new_negociation() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                START TRANSACTION;
                    INSERT INTO negociaciones VALUES (NULL, ?, ?, ?, ?, ?, 'Comentario $this->numero_negociacion: $this->comentario', ?, ?);
                    UPDATE clientes_servicios SET cliente_status = ? WHERE cliente_id = ?;
                COMMIT;
            ");
            $query->execute([ 
                $this->cliente_id, 
                $this->fecha_inicio, 
                $this->fecha_fin, 
                $this->fecha_captura, 
                $this->numero_negociacion,
                $this->status_negociacion, 
                $this->usuario_negociacion, 
                $this->cliente_status, 
                $this->cliente_id 
            ]);
            $this->set_id_negociation();
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrio un error al agregar la negociacion, la conexión a la base de datos falló!";
            //$error->getMessage();//"No se pudo completar la negociación!";
        }
    }


    public function set_id_negociation()
    {
        $this->id_negociacion = Flight::gnconn()->lastInsertId();
        return $this->id_negociacion;
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
        $ip = isset($rows[0]["mikrotik_ip"]) ? $rows[0]["mikrotik_ip"] : '';
        $user = isset($rows[0]["mikrotik_usuario"]) ? $rows[0]["mikrotik_usuario"] : '';
        $pass = isset($rows[0]["mikrotik_password"]) ? $rows[0]["mikrotik_password"] : '';
        $port = isset($rows[0]["mikrotik_puerto"]) ? $rows[0]["mikrotik_puerto"] : '';
        $metodo = isset($rows[0]["metodo_bloqueo"]) ? $rows[0]["metodo_bloqueo"] : false;

        /**
         * 
         * Establecer la conexión con el mikrotik
        */
        $conn = new Mikrotik($ip, $user, $pass, $port);

        /**
         * 
         * Si no se puede conectar al mikrotik, 
         * se inserta en la tabla mikrotik_retry_remove
         * para posteriormente ser procesado por el servicio de mikrotik
         * pero el proceso no puede continuar
         * 
        **/
        if (!$conn->connected) {
            $this->mikrotik_remove_fail($rows);
            $this->activacion = false;
        }

        /**
         * 
         * Si se puede conectar al mikrotik, se procede a 
         * activar el servicio del cliente
         * 
         */
        if ($conn->connected) {
            if ($metodo) {
                switch ($metodo) {
                    case "DHCP":
                        $conn->remove_from_address_list($rows[0]['cliente_ip'], "MOROSOS");
                        $is_exists = $conn->in_address_list($rows[0]['cliente_ip'], "MOROSOS");
                        $this->activacion = $is_exists ? false : true;
                        $conn->disconnect();
                    break;
    
                    case "ARP":
                        $conn->remove_from_address_list($rows[0]['cliente_ip'], "MOROSOS");
                        $is_exists = $conn->in_address_list($rows[0]['cliente_ip'], "MOROSOS");
                        $this->activacion = $is_exists ? false : true;
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
            $this->cliente_id, 
            'morosos'
        );

        if (empty($validate_exists)) {
            $this->insert_into_mikrotik_retry_remove(
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
        $query = Flight::gnconn()->prepare("
            SELECT * FROM $TABLE 
            WHERE cliente_id = ? 
            AND module = ?
        ");
        $query->execute([ 
            $this->cliente_id, 
            $this->module 
        ]);
        $rows = $query->fetchAll();
        return $rows;
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
            $address_list,
            $server,
            $interface_arp,
            $profile,
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
            SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, clientes.*, clientes_servicios.*, cortes_servicio.*, tipo_servicios.nombre_servicio, colonias.nombre_colonia, colonias.mikrotik_control, mikrotiks.mikrotik_nombre, paquetes.nombre_paquete, modem.modelo as modem, clientes_status.status_id, clientes_status.nombre_status FROM clientes 
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