<?php


class Payment extends Messenger
{
    public $cliente_id;
    public $pago_id;
    public $pago_monto;
    public $pago_descuento;
    public $periodo_id;
    public $concepto;
    public $usuario_captura;
    public $usuario_autorizacion;
    public $observacion;
    public $pago_fecha_captura;
    public $pago_comprobante;
    public $pago_folio;
    public $status_pago;
    public $tipo_pago;
    public $mikrotik;
    public $morosos;
    public $activation;
    public $config;
    public $error;
    public $error_message;

    public function __construct($request = [], $files = [])
    {
        $this->config = parse_ini_file('./config.ini', true);

        $this->cliente_id = isset($request->cliente_id) 
            ? $request->cliente_id 
            : null;

        $this->pago_monto = isset($request->pago_monto) 
            ? $request->pago_monto 
            : null;

        $this->pago_id = isset($request->pago_id) 
            ? $request->pago_id 
            : null;

        $this->pago_descuento = isset($request->pago_descuento) 
            && $request->pago_descuento != '' 
            ? $request->pago_descuento 
            : 0;

        $this->periodo_id = isset($request->periodo_id) 
            ? $request->periodo_id 
            : null;

        $this->concepto = "Servicio de Internet";

        $this->usuario_captura = isset($request->usuario_captura) 
            ? $request->usuario_captura 
            : null;

        $this->usuario_autorizacion = isset($request->usuario_autorizacion) 
            ? $request->usuario_autorizacion 
            : null;

        $this->observacion = isset($request->observacion) 
            ? $request->observacion 
            : null;

        $type = isset($files->comprobante['type']) 
            && !empty($files->comprobante['type'])
                ? $files->comprobante['type'] 
                : null;

        $format = !is_null($type) 
            ? explode("/", $type)[1] 
            : null;

        $periodos = !is_null($this->periodo_id) 
            ? implode("-", $this->periodo_id)
            : null;

        $new_name = !is_null($format) && !is_null($periodos) 
                ? "$this->cliente_id-$periodos.$format" 
                : null;

        $this->pago_comprobante = !is_null($new_name)
                ? $new_name
                : "no-image.png";

        $this->pago_folio = isset($request->pago_folio) 
            ? $request->pago_folio 
            : null;

        $this->status_pago = isset($request->status_pago) 
            ? $request->status_pago 
            : null;

        $this->tipo_pago = isset($request->tipo_pago) 
            ? $request->tipo_pago 
            : null;

        $this->pago_fecha_captura = isset($request->pago_fecha_captura) 
            ? $request->pago_fecha_captura 
            : date('Y-m-d');
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


    /**
     * current_year_periods
     *
     * @return array
     */
    public function current_year_periods()
    {
        $periods = [];
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(periodo_id, YEAR(CURRENT_DATE)) AS periodo_id, 
                periodos.mesinicio, 
                periodos.mesfin 
            FROM periodos
        ");
        $query->execute();
        $rows = $query->fetchAll();

        foreach($rows as $values) {
            $periods[$values['periodo_id']] = $values;
        }

        return $periods;
    }


    public function send_whatsapp_payment()
    {
        $type_message = 'pago_capturado';
        $customer = $this->get_customer_by_id();
        $template = $this->get_templates($type_message);
        $brand = $this->get_brand();
        $nombres = $customer[0]["cliente_nombres"] . " " . $customer[0]["cliente_apellidos"];
        $phone = $customer[0]["cliente_telefono"];
        $cliente_id = $customer[0]["cliente_id"];

        $periods = count($this->periodo_id) >= 2 ? implode("*", $this->periodo_id) : $this->periodo_id[0];
        $corte = $customer[0]["cliente_corte"];
        $monto = $this->pago_monto * count($this->periodo_id);
        $descuento = $this->pago_descuento * count($this->periodo_id);
        $periodos_pagados = count($this->periodo_id);
        $current_periods = $this->current_year_periods();
        $index = $this->periodo_id[$periodos_pagados - 1];
        $last_period = $current_periods[$index];
        $start_period = $current_periods[$this->periodo_id[0]];
        $first_month = $start_period["mesinicio"];
        $next_month = $last_period["mesfin"];
        $monto_pagado = number_format($monto - $descuento, 2, '.', ',');
        $folio = isset($this->pago_folio) || $this->pago_folio == "" ? "Sin folio" : $this->pago_folio;
        $tipo = $this->tipo_pago == 1 ? "Efectivo" : "Transferencia o deposito";

        $message = "*".$template[0]['title']."*\n\n". preg_replace(
            ['{{cliente}}','{{periodos}}','{{monto}}','{{fecha_captura}}','{{tipo}}','{{folio}}','{{corte}}','{{first_month}}','{{next_month}}'], 
            [$nombres, $periods, $monto_pagado, $this->pago_fecha_captura, $tipo, $folio, $corte, $first_month, $next_month],
            $template[0]["template"]
        );

        $periodos = implode("-", $this->periodo_id);
        $params = "?cliente_id=$cliente_id&periodos=$periodos";

        $voucher_data = [
            "phone" => $brand[0]['codigo_pais'].$phone,
            "message" => "Comprobante de pago!",
            "mediaUrl" => $this->config["URL_FRONTEND"]."/panel/clientes/voucher.php$params"
        ];

        $details_payment = [ 
            "phone" => $brand[0]['codigo_pais'] . $phone,  
            "message" => $message 
        ];

        $this->whatsapp($details_payment, $cliente_id, "payment");
        
        $this->whatsapp($voucher_data, $cliente_id, "voucher");
    }


    public function send_email_payment()
    {
        $type_message = 'pago_capturado';
        $customer = $this->get_customer_by_id();
        $template = $this->get_templates($type_message);
        $brand = $this->get_brand();
        $nombres = $customer[0]["cliente_nombres"] . " " . $customer[0]["cliente_apellidos"];
        $email = $customer[0]["cliente_email"];
        $phone = $customer[0]["cliente_telefono"];
        $cliente_id = $customer[0]["cliente_id"];
        $message = "<strong>Mensaje de pago!</strong>";
        // $this->Email($email, $template[0]["title"], $message, $brand[0]["resend_key"]);
    }

    /**
     * save_payment
     *
     * @return void
     **/
    public function save_payment()
    {
        /**
         * Buscar el folio ingresado 
        **/
        $payments = $this->folio != '' ? $this->repeat_folio() : [];

        if (count($payments) > 0) {
            $this->error_message = "El folio: $this->pago_folio ya existe!";
            return;
        }

        /**
         * Buscar los periodos ingresados
         */
        $periods = $this->customer_periods();

        if (count(value: $periods) > 0) {
            $this->error_message = "El periodo ya esta pagado!";
            return;
        }

        /**
         * Activar el servicio del cliente dependiendo el status
         * Que devuelva el proceso de los periodos
         */
        $this->customer_activation();
        if ($this->error) return;

        /**
         * Guardar los pagos del cliente 
         * si la activacion se realiza con exito
         */
        $this->save_in_payments();
        if ($this->error) return;

        /**
         * Guardar el pago en los movimientos de la caja
         * Solo si el root lo agrego
         */
        $this->save_movement_boxes();
        if ($this->error) return;

        /**
         * Agregar el saldo de los pagos a la caja 
         * Segun el tipo de pago
         */
        $this->add_to_boxes();
        if ($this->error) return;

        /**
         * Enviar el mensaje de pago
        **/
        $this->send_whatsapp_payment();
        // $this->send_email_payment();

        return;
    }


    /**
     * save_in_payments       Agregar pagos a la base de datos
     *
     * @return void            Retorna el status del proceso
     */
    public function save_in_payments()
    {
        $JUDGMENT = "INSERT INTO pagos VALUES ";

        foreach ($this->periodo_id as $periodo) {
            $JUDGMENT .= "(
                NULL, 
                '$this->cliente_id',
                '$this->pago_monto', 
                $this->pago_descuento, 
                '$periodo', 
                '$this->concepto', 
                '$this->usuario_captura', 
                '$this->observacion', 
                CURRENT_TIMESTAMP(), 
                '$this->pago_fecha_captura', 
                '$this->pago_fecha_captura', 
                '$this->pago_comprobante', 
                '$this->pago_folio', 
                $this->status_pago, 
                $this->tipo_pago
            ),";
        }

        try {
            $SQL = substr($JUDGMENT, 0, -1);
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute();
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();
        }
    }


    /**
     * save_movement_boxes
     * Agregar el pago a los movimientos de la caja
     * 
     * @return void
     */
    public function save_movement_boxes()
    {
        if ($this->status_pago != 1) return;

        if ($this->status_pago == 1) {
            $IN = $this->transform_array_string_in_sql($this->periodo_id);
            $rows = $this->search_payment_from_customer($IN);
            $empty_rows = empty($rows);

            if ($empty_rows) {
                $this->error = true;
                $this->error_message = "Los pagos no fueron agregados!";
            } else {
                $this->insert_movements($rows);
            }
        }
    }


    /**
     * transform_array_string_in_sql
     * Transforma un array en un string sql para sentencia IN
     * @param  mixed $periodo_id
     * @return string
     */
    public function transform_array_string_in_sql($periodo_id)
    {
        $text = json_encode($periodo_id);
        $order = array('"', '[', ']');
        $replace = array("'", "(", ")");
        $in = str_replace($order, $replace, $text);
        return $in;
    }



    public function search_payment_from_customer($in)
    {
        $SQL = "SELECT pago_id FROM pagos WHERE periodo_id IN $in AND cliente_id = '$this->cliente_id' AND YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE()) AND status_pago != 0";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }



    public function insert_movements($rows)
    {
        $JEDGMENT = "INSERT INTO movimientos_cajas VALUES ";

        for ($i = 0; $i < count($rows); $i++) {
            $pago_id = $rows[ $i ][ "pago_id" ];
            $JEDGMENT .= "(
                NULL, 
                $this->tipo_pago, 
                $pago_id, 
                1, 
                '$this->concepto', 
                CURRENT_TIMESTAMP(), 
                '$this->usuario_captura', 
                '$this->pago_monto', 
                NULL, 
                '$this->pago_comprobante', 
                '$this->pago_folio', 
                '$this->observacion', 
                $this->status_pago
            ),";
        }
        
        try {
            $SQL = substr($JEDGMENT, 0, -1);
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute();
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();
        }
    }


    /**
     * add_to_boxes           Actualizar el saldo de la caja
     *
     * @return void        Retorna el status del proceso
     * 
     */
    public function add_to_boxes()
    {
        if ($this->status_pago != 1) return;

        if ($this->status_pago == 1) {
            // TOTALES EN DESCUENTOS Y PAGOS
            $total_discount = $this->pago_descuento * count($this->periodo_id);     // Total en descuento
            $total_mensuality = $this->pago_monto * count($this->periodo_id);       // Total en mensualidad
            $total_payment = $total_mensuality - $total_discount;                   // Total en saldo
            $this->update_cajas($total_payment, $this->tipo_pago);
        }
    }


    public function update_cajas($saldo, $caja)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE cajas 
                SET saldo_actual = saldo_actual + $saldo 
                WHERE caja = ?
            ");
            $query->execute([ $caja ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar el saldo en caja!";
        }
    }


    /**
     * repeat_folio                     Verificar el folio
     * 
     * @param  string $pago_folio       Folio del pago que se busca
     * @return bool
     **/
    public function repeat_folio()
    {
        $query = Flight::gnconn()->prepare("
            SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS fullname FROM pagos 
            INNER JOIN clientes 
            ON pagos.cliente_id = clientes.cliente_id 
            WHERE pagos.pago_folio = ?
        ");
        $query->execute([ $this->pago_folio ]);
        $rows = $query->fetchAll([ $this->pago_folio ]);
        return $rows;
    }


    /**
     * customer_periods      Verificar que no se repitan los periodos
     * 
     * @return array
     **/
    public function customer_periods()
    {
        // generate IN from SQL string
        $in = $this->transform_array_string_in_sql($this->periodo_id);
        
        $query = Flight::gnconn()->prepare("
            SELECT pago_id FROM pagos 
            WHERE periodo_id IN $in 
            AND cliente_id = ? 
            AND YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE()) 
            AND status_pago != 0
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * save_payment_file       Agregar el comprobante del pago
     *
     * @param  mixed $files               Datos del archivo
     * @return boolean                       Retorna el status del proceso
     */
    public function save_payment_file($files = [])
    {
        // Si no existen datos de archivos
        if (
            !isset($files->comprobante['name']) || 
            empty($files->comprobante['name'])
        ) return true;

        $path = './assets/clientesimg/pagos/';
        $tmp_name = isset($files->comprobante['tmp_name']) 
            ? $files->comprobante['tmp_name'] 
            : null;

        return move_uploaded_file($tmp_name, $path.$this->pago_comprobante);
    }


    public function send_authorized_whatsapp() {
        $customer = $this->get_customer_by_id();
        $phone = $customer[0]["cliente_telefono"];
        $nombres = $customer[0]["cliente_nombres"] . " " . $customer[0]["cliente_apellidos"];
        $monto = number_format($this->pago_monto - $this->pago_descuento, 2, '.', ',');
        $periodo = $this->transform_array_string_in_sql($this->periodo_id);
        $message = "* ¡Pago autorizado!*\n\n*Estimado $nombres*\n\nLe informamos que su pago con los siguientes detalles:\n*Folio:* $this->pago_folio\n*Monto:* $monto\n*Periodo:* $periodo\n*Fecha de registro:* $this->pago_fecha_captura\nHa sido autorizado correctamente, le invitamos a seguir disfrutando de nuestros servicios.\n¡Gracias por ser parte de nosotros!";
    }


    public function send_decline_whatsapp() {
        $customer = $this->get_customer_by_id();
        $phone = $customer[0]["cliente_telefono"];
        $nombres = $customer[0]["cliente_nombres"] . " " . $customer[0]["cliente_apellidos"];
        $monto = number_format($this->pago_monto - $this->pago_descuento, 2, '.', ',');
        $periodo = implode("*", $this->periodo_id);
        $message = "*Pago rechazado!*\n\n*Estimado $nombres*\n\nLe informamos que su pago con los siguientes detalles:\n\n*Folio:* $this->pago_folio\n*Monto:* $monto\n*Periodo:* $periodo\n*Fecha de registro:* $this->pago_fecha_captura\n\nHa sido rechazado por el siguiente motivo:\n_*$this->observacion*_\n\nLe invitamos a ponerse en contacto con el área de finanzas para aclarar cualquier duda.\n\n*Gracias por su comprensión!*";
    }


    /**
     * authorize_payment        Authorize payment
     *
     * @param  mixed $request   Send data
     * @return void             Return true or false
     **/
    public function authorize_payment()
    {
        // Obtener los datos del pago
        $payment = $this->get_payment_by_id();

        $this->periodo_id = array($payment[0]["periodo_id"]);

        // Agregar a movimientos de la caja
        $this->add_to_moves($payment);
        if ($this->error) return;

        // Autorizar el pago
        $this->change_status_payment();
        if ($this->error) return;

        // Agregar el saldo a la caja
        $this->add_to_boxes();
        if ($this->error) return;

        $this->customer_activation();
        if ($this->error) return;
    }

    
    /**
     * get_all_payments
     *
     * @param  mixed $filters
     * @return array
     */
    public function get_all_payments($filters)
    {
        $status_pago = isset($filters['status_pago']) && strlen($filters["status_pago"]) < 12 ? $filters['status_pago'] : 'IN(1,2,3)';
        $pago_folio = isset($filters['pago_folio']) ? $filters['pago_folio'] : null;
        $tipo_pago = isset($filters['tipo_pago']) ? $filters['tipo_pago'] : null;
        $cliente_id = isset($filters['cliente_id']) ? $filters['cliente_id'] : null;
        $periodo_id = isset($filters['periodo_id']) ? $filters['periodo_id'] : null;
        $usuario_captura = isset($filters['usuario_captura']) ? $filters['usuario_captura'] : null;
        $fecha_inicio = isset($filters['date_start']) ? $filters['date_start'] : null;
        $fecha_fin = isset($filters['date_end']) ? $filters['date_end'] : null;
        $search = isset($filters['search']) ? $filters['search'] : null;
        $all = $filters['all'];


        $SQL = "
            SELECT * FROM pagos 
            LEFT JOIN usuarios
            ON pagos.usuario_captura = usuarios.usuario_id 
            LEFT JOIN empleados 
            ON usuarios.usuario_id = empleados.usuario_id 
            INNER JOIN clientes 
            ON pagos.cliente_id = clientes.cliente_id 
            WHERE pagos.status_pago $status_pago
        ";

        if (!is_null($pago_folio)) {
            $SQL .= " AND pagos.pago_folio = $pago_folio ";
        }

        if (!is_null($tipo_pago)) {
            $SQL .= " AND pagos.tipo_pago = $tipo_pago ";
        }

        if (!is_null($cliente_id)) {
            $SQL .= " AND pagos.cliente_id = '$cliente_id' ";
        }

        if (!is_null($periodo_id)) {
            $SQL .= " AND pagos.periodo_id = '$periodo_id' ";
        }

        if (!is_null($usuario_captura)) {
            $SQL .= " AND pagos.usuario_captura = '$usuario_captura' ";
        }

        if (!is_null($fecha_inicio)) {
            $SQL .= " AND DATE(pagos.pago_fecha_captura) >= DATE('$fecha_inicio') ";
        }

        if (!is_null($fecha_fin)) {
            $SQL .= " AND DATE(pagos.pago_fecha_captura) <= DATE('$fecha_fin') ";
        }

        if (!is_null($search)) {
            $SQL .= " 
                AND CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) LIKE '%$search%'
                OR CONCAT(empleados.empleado_nombre, ' ', empleados.empleado_apellido) LIKE '%$search%'
                OR pagos.pago_folio LIKE '%$search%'
                OR pagos.periodo_id LIKE '%$search%'
            ";
        }

        if ($all) {
            $SQL .= " ORDER BY pagos.status_pago DESC ";
        }

        if (!$all) {
            $SQL .= " ORDER BY pagos.status_pago DESC LIMIT 1000";
        }

        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * get_payment_by_id          Obtener pagos por su id
     *
     * @return array
     */
    public function get_payment_by_id()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM pagos 
            WHERE pago_id = ?
        ");
        $query->execute([ $this->pago_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * add_to_moves             Agregar a los movimientos de la caja
     *
     * @param  array $rows
     * @return void
     */
    public function add_to_moves($rows)
    {
        $this->pago_descuento = $rows[0]["pago_descuento"];
        $this->pago_monto = $rows[0]["pago_monto"];
        $this->tipo_pago = $rows[0]["tipo_pago"];
        $this->pago_fecha_captura = $rows[0]["pago_fecha_captura"];
        $this->pago_folio = $rows[0]["pago_folio"];
        $this->concepto = $rows[0]["concepto"];
        $comprobante = $rows[0]["pago_comprobante"];
        $observacion = $rows[0]["observacion"];

        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO movimientos_cajas 
                VALUES (NULL, ?, ?, 1, ?, CURRENT_TIMESTAMP, ?, ?, NULL, ?, ?, ?, 1)
            ");
            $query->execute([
                $this->tipo_pago,
                $this->pago_id,
                $this->concepto,
                $this->usuario_autorizacion,
                $this->pago_monto,
                $comprobante,
                $this->pago_folio,
                $observacion
            ]);
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar a los movimientos de la caja!";
        }
    }


    /**
     * decline_payment        Rechazar el pago seleccionado
     *
     * @return boolean        true/false
     */
    public function decline_payment()
    {
        // Actualizar pago a rechazado '3'
        $this->change_status_payment();
        if ($this->error) return false;

        // Pagos de los periodos actuales del cliente
        $rows = $this->payment_of_current_periods($this->cliente_id);
        // Generar el status del cliente
        $cliente_status = !empty($rows) ? 1 : 2;
        // Cambiar el status del cliente
        $this->change_customer_status($cliente_status);
        if ($this->error) return false;

        // Proceso mikrotik
        $rows = $this->get_customer_join_mikrotik();

        // Agregar o eliminar de morosos
        if ($cliente_status != 2 || $rows[0]["suspender"] == 0) {
            $this->enable_in_mikrotik($rows);
        } else {
            $this->layoff_in_mikrotik($rows);
        }

        // Asignar los datos del pago para enviar mensaje
        $payment = $this->get_payment_by_id();
        $this->periodo_id = [$payment[0]["periodo_id"]];
        $this->pago_descuento = $payment[0]["pago_descuento"];
        $this->pago_monto = $payment[0]["pago_monto"];
        $this->tipo_pago = $payment[0]["tipo_pago"];
        $this->pago_fecha_captura = $payment[0]["pago_fecha_captura"];
        $this->pago_folio = $payment[0]["pago_folio"];

        $this->send_decline_whatsapp();
        return true;
    }

    /**
     * delete_from_database
     *
     * @param  int $pago_id
     * @param  string $cliente_id
     * @return boolean
     */
    public function delete_from_database()
    {
        // Obtener los datos del pago en movimientos de la caja
        $pago = $this->get_movement_boxes_by_id($this->pago_id);

        // Si esta en los movimientos de la caja restar a la caja
        $this->error = !empty($pago) ? $this->decrement_in_boxes($pago) : false;
        if ($this->error) return false;

        // Eliminar el pago de pagos se eliminara automaticamente el movimiento
        $this->delete_from_payment();
        if ($this->error) return false;

        // Pagos de los periodos actuales del cliente
        $rows = $this->payment_of_current_periods($this->cliente_id);
        $cliente_status = !empty($rows) ? 1 : 2; // Status del cliente

        $this->change_customer_status($cliente_status);
        if ($this->error) return false;

        $rows = $this->get_customer_join_mikrotik();

        if ($cliente_status == 2) {
            $this->layoff_in_mikrotik($rows);
        } else {
            $this->enable_in_mikrotik($rows);
        }

        return true;
    }

    /**
     * get_movement_boxes_by_id         Obtener movimiento de la caja por id pago
     *
     * @param  int $pago_id             Id del pago a buscar
     * @return array
     */
    public function get_movement_boxes_by_id($pago_id)
    {
        $SQL = " SELECT * FROM movimientos_cajas WHERE pago_id = ?";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $this->pago_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * decrement_in_boxes          Decrementar saldo de la caja
     *
     * @param  array $pago        Pago de referencia
     * @return void               Respuesta de la funcion
     */
    public function decrement_in_boxes($pago) : void
    {
        try {
            $caja = isset($pago[0]["caja"]) ? $pago[0]["caja"] : 1;
            $monto = isset($pago[0]["monto_movimiento"]) ? $pago[0]["monto_movimiento"] : 0;
            $query = Flight::gnconn()->prepare("
                UPDATE cajas 
                SET saldo_actual = saldo_actual - $monto 
                WHERE caja = ?
            ");
            $query->execute([ $caja ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar caja pago!";
        }
    }

    /**
     * delete_from_payment
     *
     * @return void
     */
    public function delete_from_payment() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                DELETE FROM pagos 
                WHERE pago_id = ?
            ");
            $query->execute([ $this->pago_id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al eliminar pago!";
        }
    }

        
    /**
     * change_status_payment
     *
     * @return void
     */
    public function change_status_payment()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE pagos 
                SET status_pago = ?,
                observacion = ?,
                pago_fecha_validacion = CURRENT_TIMESTAMP
                WHERE pago_id = ?
            ");
            $query->execute([
                $this->status_pago,
                $this->observacion,
                $this->pago_id
            ]);
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = "Error al intentar modificar el pago!";
        }
    }



    /**
     * customer_activation
     *
     * Activar al cliente que realiza el pago
     * @return void
     */
    public function customer_activation()
    {        
        /**
         * Obtener los datos del cliente junto con los datos del mikrotik
        **/
        $customer = $this->get_customer_join_mikrotik();

        $cliente_status = $customer[0]["cliente_status"];

        /**
         * Definir el status del cliente segun el status del pago
        **/
        $new_status = $this->status_pago == 2 ? 5 : 1;

        /**
         * Se debe cambiar el status del cliente?
        **/
        $results = $this->is_changeable_status($customer);

        switch($cliente_status) {
            case 1:
                if ($results == 'change') {
                    /**
                     * Cambiar el status si es necesario
                    **/
                    $this->change_customer_status($new_status);
                }
                /**
                 * Habilitar en el mikrotik
                **/
                $this->enable_in_mikrotik($customer);
            break;

            case 2: 
                /**
                 * Si el status debe cambiarse
                **/
                if ($results == 'change') {
                    /**
                     * Cambiar el estatus del cliente
                     */
                    $this->change_customer_status($new_status);
                    /**
                     * Habilitar en el mikrotik
                    **/
                    $this->enable_in_mikrotik($customer);
                }

                /**
                 * Si el status se debe mantener en este status
                 */
                if ($results == 'keep') {
                    /**
                     * Bloquear en el mikrotik 
                    **/
                    $this->layoff_in_mikrotik($customer);
                }
            break;
            case 3: 
                $this->error = true;
                $this->error_message = "Esta capturando un pago a un cliente inactivo!";
            case 4: 
                /**
                 * Si el status debe cambiarse
                **/
                if ($results == 'change') {
                    /**
                     * Cambiar el estatus del cliente
                     */
                    $this->change_customer_status($new_status);
                }

                /**
                 * Habilitar en el mikrotik
                **/
                $this->enable_in_mikrotik($customer);
            break;
            case 5:
                /**
                 * Si el status debe cambiarse
                **/
                if ($results == 'change') {
                    /**
                     * Cambiar el estatus del cliente
                     */
                    $this->change_customer_status($new_status);
                }

                /**
                 * Habilitar en el mikrotik
                **/
                $this->enable_in_mikrotik($customer);
            break;
            case 6: 
                /**
                 * Habilitar en el mikrotik
                **/
                $this->enable_in_mikrotik($customer);
            break;
            default: 
                // No se hace nada
            break;
        }

        

        

        /**
         * El status debe mantenerse 
        **/
        if ($results == 'change' && $cliente_status == 2) {

        }

        /**
         * Los procesos seran diferentes 
         * segun el status del cliente
        **/
        // switch($cliente_status) {
        //     case 'change': 
        //         $this->finally_pending_negociations();
        //         $this->finally_running_negociations($cliente_status);
        //         $this->enable_in_mikrotik($customer);


        //         if ($todo == 'change') {
        //             $this->change_customer_status($new_status);
        //         }
        //     break;
        //     case 'keep':
        //         $this->is_changeable_status($customer);
        //     break;
        //     default:
        //         $this->error = true;
        //         $this->error_message = "No se especifico el status del cliente!";
        // }


        // Buscar pagos solo en periodo actual
        // $current_payment = $this->payment_of_current_periods($this->cliente_id);
        // // Buscar pagos en periodo anterior y periodo actual
        // $current_two_payment = $this->payment_of_current_two_periods($this->cliente_id);

        // if ($cliente_status == 1 || $cliente_status == 6) {
        //     $this->finally_pending_negociations();
        //     $this->finally_running_negociations($cliente_status);
        //     $this->enable_in_mikrotik($customer);
        //     return;
        // }

        // else if ($cliente_status == 2 && empty($current_two_payment)) {
        //     $this->layoff_in_mikrotik($customer);
        //     $this->change_customer_status(2);
        //     return;
        // }

        // else if ($cliente_status == 2 && !empty($current_two_payment)) {
        //     $this->change_customer_status($new_status);
        //     $this->enable_in_mikrotik($customer);
        //     return;
        // }

        // else if ($cliente_status == 4) {
        //     $this->finally_running_negociations($new_status);
        //     $this->enable_in_mikrotik($customer);
        //     return;
        // }

        // else if ($cliente_status == 5 && empty($current_two_payment)) {
        //     $this->finally_pending_negociations();
        //     $this->finally_running_negociations($cliente_status);
        //     $this->layoff_in_mikrotik($customer);
        //     $this->change_customer_status(2);
        //     return;
        // }

        // else if ($cliente_status == 5 && !empty($current_two_payment)) {
        //     $this->change_customer_status($new_status);
        //     $this->enable_in_mikrotik($customer);
        //     return;
        // }
    }


    public function is_changeable_status($customer)
    {
        // Dia actual
        $today = intval(date('d'));

        // Dia de pago del cliente
        $payment_day = $customer[0]['cliente_corte'];

        // Este es el periodo anterior
        $periodo_anterior = $this->get_before_period();

        // Este es el periodo actual
        $periodo_actual = $this->get_current_period();

        // Este es el periodo que sigue
        $periodo_siguiente = $this->get_after_period();

        /**
         * Es un periodo
        **/
        $is_before = in_array($periodo_anterior, $this->periodo_id);

        /**
         * Es un periodo actual
        **/
        $is_current = in_array($periodo_actual, $this->periodo_id);

        /**
         * Es un periodo posterior
        **/
        $is_next = in_array($periodo_siguiente, $this->periodo_id);

        /**
         * Comenzar a validar los periodos entregados
        **/
        if (count($this->periodo_id) == 1) {
            /**
             * Si el pago es anterior entonces verificar que aun no ha terminado su periodo
             *  si ya termino no se cambia el status
            **/
            if ($is_before && $payment_day >= $today) {
                return 'change';
            }
            /**
             * Si esta pagando el periodo anterior y ya paso la fecha de pago
             * Entonces se mantiene el status actual ej: si esta suspendido 
             * Se mantiene suspendido
            **/
            else if ($is_before && $payment_day < $today) {
                return 'keep';
            }

            /**
             * Si esta pagando un periodo actual pero aun no llega su fecha de pago entonces
             * El status de deja tal y como esta ej: si esta activo se queda activo 
             */
            else if ($is_current && $payment_day > $today) {
                return 'keep';
            }

            /**
             * 
             * Si el pago es del periodo actual y su fecha de pago ya paso
             * Cambiar el status del cliente por el correspondiente
             * 
            **/
            else if ($is_current && $payment_day <= $today) {
                return 'change';   
            }

            /**
             * Si el pago es del periodo posterior el status del cliente 
             * No debe tener ningun cambio solo el status del pago
            **/
            else if ($is_next) {
                return 'keep';   
            }

            /**
             * Si no es de ningun periodo entonces mantener el status
             */
            if (!$is_before && !$is_current && !$is_next) {
                return 'keep';
            }
        }
        
        if (count($this->periodo_id) >= 2) {
            /**
             * Si dentro de los pagos enviados se encuentran el anterior y el actual
             * O si se encuentran el anterior, el actual y el siguiente por pagar
             * Entonces el cliente debe cambiar de status
            **/
            if ($is_before && $is_current || $is_before && $is_current && $is_next) {
                return 'change';
            }

            /**
             * Si esta el pago anterior y no esta ni el siguiente ni el actual 
             * Y la fecha de pago aun no pasa se cambia el status
            **/
            else if ($is_before && !$is_current && !$is_next && $payment_day >= $today) {
                return 'change';
            }

            /**
             * Si el pago es anterior y el dia de pago ya paso
             * Entonces el status debe mantenerse
            **/
            else if ($is_before && !$is_current && !$is_next && $payment_day < $today) {
                return 'keep';
            }

            /**
             * Si solo esta el periodo anterior y el siguiente
             */
            else if ($is_before && !$is_current && $is_next && $payment_day >= $today) {
                return 'change';
            }

            /**
             * Si solo esta el periodo anterior y el siguiente
             */
            else if ($is_before && !$is_current && $is_next && $payment_day < $today) {
                return 'keep';
            }

            /**
             * El pago es actual y el dia de pago aun no llega
            **/
            else if (!$is_before && $is_current && !$is_next && $payment_day >= $today) {
                return 'keep';
            }

            /**
             * El pago es actual y el dia de pago ya paso
            **/
            else if (!$is_before && $is_current && !$is_next && $payment_day < $today) {
                return 'change';
            }

            /**
             * El pago es actual y el dia de pago aun no llega
            **/
            else if (!$is_before && $is_current && $is_next && $payment_day >= $today) {
                return 'keep';
            }

            /**
             * El pago es actual y el dia de pago ya paso
            **/
            else if (!$is_before && $is_current && $is_next && $payment_day < $today) {
                return 'change';
            }

            /**
             * Si es un pago actual y otro que no es el siguiente ni el anterior y la fecha de pago
             * Ya paso, entonces el status del cliente debe cambiar
            **/
            else if (!$is_before && !$is_current && $is_next) {
                return 'keep';
            }

            /**
             * Si no es de ninguno de los periodos marcados no hacer nada
             */
            else if (!$is_before && !$is_current && !$is_next) {
                return 'keep';
            }
        }
    }


    /**
     * Finaliza las negociaciones pendientes que coinciden con el período actual.
     * 
     * Esta función busca todas las negociaciones pendientes del cliente y las compara con los períodos actuales.
     * Si una negociación coincide con el período actual, se finaliza automáticamente.
     * 
     * @return void
     */
    public function finally_pending_negociations() 
    {
        /**
         * Buscar negociaciones pendientes
        **/
        $negociaciones = $this->get_negociations_pending();

        /**
         * Si el período de la negociación es igual al pago
         * Esta debe finalizarse
        **/
        if (count($negociaciones) > 0) {
            foreach($negociaciones as $negociacion) {
                $fecha_inicio = explode('-', $negociacion['fecha_inicio']);
                $periodo_negociacion = $fecha_inicio[1].$fecha_inicio[0];
                if (in_array($periodo_negociacion, $this->periodo_id)) {
                    $this->end_negociation_id($negociacion['id_negociacion']);
                }
            }
        }
    }


    public function finally_running_negociations() 
    {
        /**
         * Buscar negociaciones pendientes
        **/
        $negociaciones = $this->get_negociations_running();

        /**
         * Si el período de la negociación es igual al pago
         * Esta debe finalizarse
        **/
        if (count($negociaciones) > 0) {
            foreach($negociaciones as $negociacion) {
                $fecha_inicio = explode('-', $negociacion['fecha_inicio']);
                $periodo_negociacion = $fecha_inicio[1].$fecha_inicio[0];
                if (in_array($periodo_negociacion, $this->periodo_id)) {
                    $this->end_negociation_id($negociacion['id_negociacion']);
                }
            }
        }
    }



    public function get_negociations_pending()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM negociaciones
            WHERE status_negociacion = 2
            AND cliente_id = ?
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_negociations_running()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM negociaciones
            WHERE status_negociacion = 1
            AND cliente_id = ?
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * end_negociation    Finalizar una negociacion
     * Negociaciones
     * 0 Rechazada
     * 1 Corriendo
     * 2 Revision
     * 3 Finalizada
     * @return void
     **/
    public function end_negociation() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE negociaciones 
                SET status_negociacion = 3,
                fecha_fin = CURRENT_DATE()
                WHERE cliente_id = ? 
                AND status_negociacion = 1
            ");
            $query->execute([ $this->cliente_id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al finalizar la negociacion!";
        }
    }


    /**
     * end_negociation    Finalizar una negociacion
     * Negociaciones
     * 0 Rechazada
     * 1 Corriendo
     * 2 Revision
     * 3 Finalizada
     * @return void
     **/
    public function end_negociation_id($id_negociacion) : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE negociaciones 
                SET status_negociacion = 3,
                fecha_fin = CURRENT_DATE()
                WHERE id_negociacion = ?
            ");
            $query->execute([ $id_negociacion ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al finalizar la negociacion!";
        }
    }


    /**
     * layoff_in_mikrotik
     *
     * @param  array $rows
     * @return mixed
     */
    public function layoff_in_mikrotik($rows)
    {
        if ($rows[0]["suspender"] == 0) {
            $this->morosos = true;
            return;
        }

        $cliente_nombres = $rows[0]['cliente_nombres']." ".$rows[0]['cliente_apellidos'];
        $ip = $rows[0]['mikrotik_ip'];
        $user = $rows[0]['mikrotik_usuario'];
        $password = $rows[0]['mikrotik_password'];
        $puerto = $rows[0]['mikrotik_puerto'];

        $conn = new Mikrotik($ip, $user, $password, $puerto);
        if (!$conn->connected) {
            $this->mikrotik_add_fail($rows);
            $this->morosos = false;
            return;
        } 

        switch ($rows[0]["metodo_bloqueo"]) {
            case "DHCP":
                $this->morosos = true;
                $conn->add_from_address_list(
                    $cliente_nombres,
                    $rows[0]['cliente_ip'],
                    "MOROSOS"
                );
                $conn->disconnect();
            break;

            case "ARP":
                $this->morosos = true;
                $conn->add_from_address_list(
                    $cliente_nombres,
                    $rows[0]['cliente_ip'],
                    "MOROSOS"
                );
                $conn->disconnect();
            break;

            case "PPPOE":
                $this->morosos = true;
                $conn->disabled_secret(
                    $rows[0]['user_pppoe'],
                    $rows[0]['profile']
                );
                $conn->disconnect();
            break;
            default: // Do nothing
        }
    }

    /**
     * get_customer_join_mikrotik
     *
     * @return array
     */
    public function get_customer_join_mikrotik()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.cliente_id, 
                clientes.cliente_nombres, 
                clientes.cliente_apellidos, 
                clientes.cliente_email,
                clientes.server,
                clientes.interface_arp, 
                clientes.profile, 
                clientes.user_pppoe,
                clientes.password_pppoe,
                CONCAT(clientes.cliente_nombres,  ' ', clientes.cliente_apellidos) AS nombres, 
                clientes_servicios.cliente_corte, 
                mikrotiks.mikrotik_id, 
                mikrotiks.mikrotik_ip, 
                mikrotiks.mikrotik_usuario, 
                mikrotiks.mikrotik_password, 
                mikrotiks.mikrotik_puerto, 
                clientes.cliente_ip, 
                clientes.metodo_bloqueo, 
                clientes_servicios.cliente_status, 
                clientes.cliente_mac, 
                clientes_servicios.tipo_servicio, 
                clientes_servicios.suspender 
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
     * payment_of_current_periods
     * Obten los pagos más actuales de un cliente 
     * @param  mixed $to_periods
     * @return array
     */
    public function payment_of_current_periods($cliente_id)
    {
        $to_periods = $this->get_current_period();
        $before_periods = $this->get_before_period();
        $periods = [$to_periods, $before_periods];

        $IN_PERIODS = $this->transform_array_string_in_sql($periods);

        
        $query = Flight::gnconn()->prepare("
            SELECT pago_id 
            FROM pagos 
            WHERE periodo_id 
            IN $IN_PERIODS 
            AND cliente_id = '$cliente_id' 
            AND status_pago != 0
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function payment_of_current_two_periods($cliente_id)
    {
        $to_periods = $this->get_current_period();
        $before_periods = $this->get_before_period();
        $periods = [$to_periods, $before_periods];

        $IN_PERIODS = $this->transform_array_string_in_sql($periods);
        
        $query = Flight::gnconn()->prepare("
            SELECT pago_id 
            FROM pagos 
            WHERE periodo_id 
            IN $IN_PERIODS 
            AND cliente_id = '$cliente_id' 
            AND status_pago != 0
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }



    /**
     * get_current_period
     * 
     * Obtener el periodo actual
     *
     * @return string
     */
    public function get_current_period() {
        $query = Flight::gnconn()->prepare("
            SELECT 
            DATE(CURRENT_DATE) 
            AS to_date
        ");
        $query->execute();
        $rows = $query->fetchAll();

        $fecha = explode("-", $rows[0]["to_date"]);

        $periodo_actual = $fecha[1].$fecha[0];

        return $periodo_actual;
    }

    /**
     * get_after_period
     * 
     * Obtener los periodos posteriores
     * 
     * @return string
     */
    public function get_after_period() {
        $query = Flight::gnconn()->prepare("
            SELECT 
            DATE(DATE_ADD(CURRENT_DATE, INTERVAL +1 MONTH)) 
            AS after_date
        ");
        $query->execute();
        $rows = $query->fetchAll();

        $fecha = explode("-", $rows[0]["after_date"]);

        $periodo_posterior = $fecha[1].$fecha[0];

        return $periodo_posterior;
    }

        
    /**
     * get_before_period
     * Obtener el periodo anterior
     * 
     * @return string
     */
    public function get_before_period() {
        $query = Flight::gnconn()->prepare("
            SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL -1 MONTH)) 
            AS before_date
        ");
        $query->execute();
        $rows = $query->fetchAll();

        $fecha = explode("-", $rows[0]["before_date"]);

        $periodo_anterior = $fecha[1].$fecha[0];

        return $periodo_anterior;
    }


    public function change_customer_status($new_status)
    {
        try {
            $is_suspended = $new_status == 2 ? "CURRENT_TIMESTAMP()" : "ultima_suspension";
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios 
                SET cliente_status = ?,
                ultima_suspension = ?
                WHERE cliente_id = ?
            ");
            $query->execute([
                $new_status,
                $is_suspended,
                $this->cliente_id
            ]);
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = "No pudimos activar al cliente!";
        }
    }

    
    /**
     * mikrotik_add_fail
     *
     * @param  mixed $mikrotik_data
     * @return void
     */
    public function mikrotik_add_fail ($mikrotik_data)
    {
        $is_exists = $this->this_record_already_exists(
            'mikrotik_retry_add', 
            $mikrotik_data[0]['cliente_id'], 
            'morosos'
        );

        !$is_exists && $this->insert_into_mikrotik_retry_add(
            $mikrotik_data[0]['cliente_id'], 
            $mikrotik_data[0]['mikrotik_id'], 
            $mikrotik_data[0]['server'], 
            $mikrotik_data[0]['interface_arp'], 
            $mikrotik_data[0]['profile'], 
            'morosos'
        );
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
            'morosos'
        );
    }


    /**
     * insert_into_mikrotik_retry
     * 
     * Guardar los datos que no pudieron guardarse en el mikrotik para agregarlos despues
     * @param  mixed $cliente_id  Solo se necesita el id del cliente
     * @return void
     */
    public function insert_into_mikrotik_retry_add($cliente_id, $mikrotik_id, $server, $interface_arp, $profile, $module)
    {
        $query = Flight::gnconn()->prepare("
            INSERT INTO mikrotik_retry_add 
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $query->execute([
            $cliente_id,
            $mikrotik_id,
            $server, 
            $interface_arp, 
            $profile,
            $module
        ]);
    }


    /**
     * this_record_already_exists
     *
     * Verifica que no exista el registro
     * @param  mixed $TABLE
     * @param  mixed $cliente_id
     * @param  mixed $module
     * @return bool
     */
    public function this_record_already_exists($TABLE, $cliente_id, $module)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM $TABLE WHERE 
            cliente_id = ? 
            AND module = ?
        ");

        $query->execute([
            $cliente_id,
            $module
        ]);

        $rows = $query->fetchAll();
        $exists = empty($rows);
        if ($exists) return false;
        return true;
    }


    /**
     * insert_into_mikrotik_retry_remove
     *
     * Guardar los datos que no se pudieron eliminar el el mikrotik
     * para eliminarlos cuando este en linea nuevamente
     * 
     * @param  mixed $cliente_id  Cliete id para obtener los datos
     * @param  mixed $cliente_ip  Direccion IPv4 que se buscará en el mikrotik
     * @param  mixed $cliente_mac  Direccion mac que se buscará para eliminar
     * @param  mixed $module  Modulo de mikrotik donde se eliminarán los datos
     * @return void
     */
    public function insert_into_mikrotik_retry_remove($cliente_id, $mikrotik_id, $cliente_ip, $cliente_mac, $server,  $interface_arp, $profile, $address_list, $module) 
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
     * Obtener los datos del cliente que esta realizando el pago
     * 
     * @return array
     */
    public function get_customer_by_id()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.*, 
                clientes_servicios.*, 
                tipo_servicios.nombre_servicio, 
                colonias.nombre_colonia, 
                colonias.mikrotik_control, 
                mikrotiks.mikrotik_nombre, 
                paquetes.nombre_paquete, 
                modem.modelo as modem, 
                clientes_status.status_id, 
                clientes_status.nombre_status 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN tipo_servicios 
            ON clientes_servicios.tipo_servicio = tipo_servicios.servicio_id 
            INNER JOIN colonias 
            ON colonias.colonia_id = clientes_servicios.colonia 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            INNER JOIN clientes_status 
            ON clientes_servicios.cliente_status = clientes_status.status_id 
            INNER JOIN paquetes 
            ON paquetes.idpaquete = clientes_servicios.cliente_paquete 
            CROSS JOIN status_equipo 
            ON clientes_servicios.status_equipo = status_equipo.status_id 
            INNER JOIN cortes_servicio 
            ON cortes_servicio.corte_id = clientes_servicios.cliente_corte 
            CROSS JOIN modem 
            ON clientes_servicios.modem_instalado = modem.idmodem 
            WHERE clientes.cliente_id = ?
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * enable_in_mikrotik     Habilitar el servici del cliente en el mikrotik
     *
     * @param  array $rows
     * @return void
     */
    public function enable_in_mikrotik($rows)
    {
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $port = $rows[0]["mikrotik_puerto"];

        $conn = new Mikrotik($ip, $user, $password, $port);
        if (!$conn->connected) {
            $this->activation = false;
            $this->mikrotik_remove_fail($rows);
            return;
        }

        switch ($rows[0]["metodo_bloqueo"]) {
            case "DHCP":
                $conn->remove_from_address_list($rows[0]["cliente_ip"], "MOROSOS");
                $in_list = $conn->in_address_list($rows[0]['cliente_ip'], 'MOROSOS');
                /**
                 * ¿Continua en morosos?
                 */
                if (!$in_list) {
                    $this->activation = true;
                } 

                /**
                 * Ya no esta en morosos
                 */
                if ($in_list) {
                    $this->activation = false;
                }

                /**
                 * Desconectar mikrotik
                 */
                $conn->disconnect();
            break;

            case "ARP":
               $conn->remove_from_address_list($rows[0]["cliente_ip"], "MOROSOS");
                $in_list = $conn->in_address_list($rows[0]['cliente_ip'], 'MOROSOS');
                /**
                 * ¿Continua en morosos?
                 */
                if (!$in_list) {
                    $this->activation = true;
                } 
                
                /**
                 * Ya no esta en morosos
                 */
                if ($in_list) {
                    $this->activation = false;
                }

                /**
                 * Desconectar mikrotik
                 */
                $conn->disconnect();
            break;

            case "PPPOE":
                $conn->enable_secret($rows[0]["user_pppoe"], $rows[0]['profile']);
                $this->activation = true;
                $conn->disconnect();
            break;
            default: // Mostrar nada
        }
    }

}
