<?php

class ReadPayment
{
    public $customer_data;
    public $periodos;
    public $promosiones;
    public $earrings;
    public $authorized;
    public $count_all;
    public $cliente_id;
    public $dia_pago;
    public $colonia_id;
    public $periodo_actual;
    public $physical;
    public $transfer;
    public $type_payments;
    public $rejected;
    public $month_promotions;
    public $details;


    /**
     * __construct     Construir el objeto
     * @param string $cliente_id   Cliente en cuestion
     * @return void
     **/
    public function __construct($cliente_id = null)
    {
        $this->cliente_id = isset($cliente_id) ? $cliente_id : null;
    }

    /**
     * __get
     *
     * @param  mixed $propiedad
     * @return mixed
     */
    public function __get($propiedad)
    {
        if (property_exists($this, $propiedad)) {
            return $this->$propiedad;
        }
    }

    /**
     * __set
     *
     * @param  mixed $propiedad
     * @param  mixed $valor
     * @return void
     */
    public function __set($propiedad, $valor)
    {
        if (property_exists($this, $propiedad)) {
            $this->$propiedad = $valor;
        }
    }

    /**
     * count_totals_payments      Contar los pagos del mes, año, periodo, etc
     *
     * @return void
     */
    public function count_totals_payments()
    {
        $this->earrings = $this->count_payments_by_status(2);
        $this->authorized = $this->count_payments_by_status(1);
        $this->rejected = $this->count_payments_by_status(0);
        $this->physical = $this->count_payments_by_type(1);
        $this->transfer = $this->count_payments_by_type(2);
        $this->count_all = $this->count_payments();
    }

    /**
     * count_payments     Contar todos los pagos del año
     *
     * @return array
     */
    public function count_payments()
    {
        $SQL = "SELECT COUNT(pagos.pago_id) AS total FROM pagos WHERE YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE())";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * count_payments_by_status
     *
     * @param  int $status
     * @return array
     */
    public function count_payments_by_status($status)
    {
        $SQL = "SELECT COUNT(pagos.pago_id) AS total, pagos.status_pago FROM pagos WHERE YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE()) AND status_pago = $status";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * count_payments_by_type
     *
     * @param  int $type
     * @return array
     */
    public function count_payments_by_type($type)
    {
        $SQL = "SELECT COUNT(pagos.pago_id) AS total, pagos.status_pago FROM pagos WHERE YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE()) AND tipo_pago = $type";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_resources_customer       Obtner lista de recursos para capturar pago
     *  
     * @return void
     */
    public function get_resources_customer()
    {
        $this->customer_data = $this->get_customer_cut_data();       // Obtener los datos del corte del cliente
        $this->periodo_actual = $this->get_to_periods();              // Obtener el periodo actual
        $this->periodos = $this->get_customer_periods();             // Obtener los periodos del cliente
        $this->promosiones = $this->get_promotions_customer();       // Obtener las promosiones
        $this->month_promotions = $this->get_period_promotions();    // Periodos validos para promosiones
        $this->type_payments = $this->type_payments();               // Obtener los tipos de pago
    }

    /**
     * get_all_payment                  Seleccionar todos los ordenados por status
     *
     * @param  int $status_pago         Status que se buscará en la tabla
     * @return array
     */
    public function get_all_payment($status_pago = 0)
    {
        $SQL = "
            SELECT * FROM pagos 
            CROSS JOIN usuarios 
            ON pagos.usuario_captura = usuarios.usuario_id 
            LEFT JOIN empleados 
            ON usuarios.usuario_id = empleados.usuario_id 
            INNER JOIN clientes 
            ON pagos.cliente_id = clientes.cliente_id 
            WHERE YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE())
        ";

        if (!is_null($status_pago)) {
            $SQL .= " 
                AND pagos.status_pago = ? 
                ORDER BY pagos.pago_fecha_captura 
                DESC LIMIT 1000
            ";
        } else {
            $SQL .= " 
                AND pagos.status_pago != ? 
                ORDER BY pagos.pago_fecha_captura 
                DESC LIMIT 1000
            ";
        }

        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $status_pago ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_payment_by_type          Seleccionar pagos ordenados por tipos
     *
     * @param  int $type
     * @return array
     */
    public function get_payment_by_type($type)
    {
        $SQL = " SELECT * FROM pagos CROSS JOIN usuarios ON pagos.usuario_captura = usuarios.usuario_id INNER JOIN empleados ON usuarios.usuario_id = empleados.usuario_id CROSS JOIN clientes ON pagos.cliente_id = clientes.cliente_id WHERE YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE())";

        if (!is_null($type)) {
            $SQL .= " AND pagos.tipo_pago = $type ORDER BY pagos.pago_fecha_captura DESC LIMIT 500";
        } else {
            $SQL .= " ORDER BY pagos.pago_fecha_captura DESC LIMIT 500";
        }

        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * search_payment_params         Buscar un pago por parametros [folio, nombre_cliente]
     *
     * @param  mixed $search_params  Parametros a buscar
     * @return array
     */
    public static function search_payment_params($search_params)
    {
        if (!isset($search_params))
            return array();
        $SQL = "SELECT * FROM pagos CROSS JOIN usuarios ON pagos.usuario_captura = usuarios.usuario_id INNER JOIN empleados ON usuarios.usuario_id = empleados.usuario_id CROSS JOIN clientes ON pagos.cliente_id = clientes.cliente_id WHERE CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) LIKE '%$search_params%' OR pagos.pago_folio LIKE '%$search_params%' OR CONCAT(empleados.empleado_nombre, ' ', empleados.empleado_apellido) LIKE '%$search_params%' ORDER BY pagos.pago_fecha_captura DESC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
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
     * payment_customer                 Obtener el historial del cliente
     *
     * @param  string $cliente_id
     * @return array
     */
    public function payment_customer($cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM pagos 
            WHERE cliente_id = ? 
            ORDER BY status_pago DESC
        ");
        $query->execute([ $cliente_id ]);
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
    public function get_customer_periods()
    {
        $SQL = "SELECT string FROM cortes_servicio INNER JOIN clientes_servicios ON cortes_servicio.corte_id = clientes_servicios.cliente_corte WHERE clientes_servicios.cliente_id = '$this->cliente_id'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $string = isset($rows[ 0 ][ "string" ]) ? $rows[ 0 ][ "string" ] : "A";
        if ($string) {
            $SQL = " SELECT * FROM periodos WHERE NOT EXISTS ( SELECT pago_id FROM pagos WHERE cliente_id = '$this->cliente_id' AND YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE()) AND pagos.periodo_id = periodos.periodo_id AND pagos.status_pago != 0) AND periodo_id LIKE '%$string%'";
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute();
            $rows = $query->fetchAll();
            return $rows;
        }
        return array();
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
        $colonia_id = $this->customer_data[ 0 ][ "colonia" ];
        $SQL = " SELECT * FROM promosiones WHERE colonia_id = $colonia_id";
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
        $fecha = explode("-", $rows[ 0 ][ "to_date" ]);
        $periodo_actual = $fecha[ 1 ] . $fecha[ 0 ];
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

    /**
     * get_to_periods
     *
     * @return array
     */
    public function get_period_promotions()
    {
        $dates = array();
        $tomonth = intval(date('m'));
        $months = 0;
        for ($i = $tomonth; $i < 12; $i++) {
            $months++;
            $SQL = "SELECT DATE(date_add(CURRENT_DATE(), INTERVAL + $months MONTH)) AS month, cortes_servicio.* FROM cortes_servicio";
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute();
            $rows = $query->fetchAll();
            foreach ($rows as $cut) {
                $period_month = explode("-", $cut[ "month" ])[ 1 ];
                array_push($dates, $period_month . $cut[ "string" ]);
            }
        }
        return $dates;
    }


    /**
     * SearchFolio
     * 
     * @param string $folio
     * @return array
     */
    public function search_folio($folio)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM pagos 
            INNER JOIN clientes 
            ON pagos.cliente_id = clientes.cliente_id 
            WHERE pagos.pago_folio = ?
        ");
        $query->execute([ $folio ]);
        $rows = $query->fetchAll();
        return $rows;
    }



    /**
     * SearchFolio
     * Buscar un periodo de un cliente
     * @param  mixed $folio
     * @return array
     */
    public function search_period($periodo_id, $cliente_id)
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS names FROM pagos INNER JOIN clientes ON pagos.cliente_id = clientes.cliente_id WHERE pagos.periodo_id = '$periodo_id' AND pagos.cliente_id = '$cliente_id' AND YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE()) AND pagos.status_pago != 0";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

}

?>