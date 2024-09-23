<?php

class Dashboard
{

    public $customers;
    public $payments;
    public $status;
    public $failures;
    public $colognes;
    public $mikrotiks;


    public function __construct()
    {

    }

    public function __get($prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }
    }

    public function __set($prop, $value)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop = $value;
        }
    }


    public function get_status_customers()
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(clientes_servicios.cliente_id) AS total_clientes, clientes_status.* FROM clientes_status
            LEFT JOIN clientes_servicios 
            ON clientes_status.status_id = clientes_servicios.cliente_status
            GROUP BY clientes_status.status_id ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_customers_group_status() 
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(clientes_servicios.cliente_id) AS total, clientes_servicios.cliente_status, clientes_status.nombre_status FROM clientes_servicios
            INNER JOIN clientes_status 
            ON clientes_servicios.cliente_status = clientes_status.status_id
            GROUP BY clientes_servicios.cliente_status ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_failures_priority() 
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM reportes_fallas
            INNER JOIN clientes
            ON reportes_fallas.cliente_id = clientes.cliente_id
            INNER JOIN empleados 
            ON reportes_fallas.tecnico = empleados.empleado_id
            INNER JOIN status_reportes
            ON reportes_fallas.status = status_reportes.id
            INNER JOIN prioridad_reportes
            ON reportes_fallas.prioridad = prioridad_reportes.id
            WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE)
            AND reportes_fallas.status = 1
            AND prioridad = 1
            ORDER BY reportes_fallas.status DESC
            LIMIT 10
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_mikrotiks_active() 
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM mikrotiks
            WHERE mikrotik_status = 1
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function home() 
    {
        $period = $this->get_to_period();
        $status = $this->get_status_customers();
        $priority = $this->get_failures_priority();
        $failures = $this->get_failures_current_year();
        $mikrotiks = $this->get_mikrotiks_active();
        $payments = $this->count_payments_periods();
        $waiting = $this->get_wait_balance();
        $registered = $this->get_register_balance($period[0]);

        return array(
            "status" => $status,
            "mikrotiks" => $mikrotiks,
            "payments" => [
                "periods" => $payments,
                "waiting" => $waiting,
                "registered" => $registered
            ],
            "failures" => [
                "priority" => $priority,
                "current" => $failures
            ]
        );
    }


    public function get_failures_current_year()
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(reporte_id) AS total, MONTH(fecha_captura) AS month FROM reportes_fallas 
            WHERE YEAR(fecha_captura) = YEAR(CURRENT_DATE)
            GROUP BY month ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_failures_by_priority()
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(reportes_fallas.reporte_id) AS total, prioridad_reportes.prioridad_reporte AS prioridad FROM prioridad_reportes 
            INNER JOIN reportes_fallas ON prioridad_reportes.id = reportes_fallas.prioridad
            WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE)
            GROUP BY prioridad_reportes.id ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_failures_by_status()
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(reportes_fallas.reporte_id) AS total, status_reportes.status_reporte AS status FROM status_reportes 
            INNER JOIN reportes_fallas ON status_reportes.id = reportes_fallas.status
            WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE)
            GROUP BY status_reportes.id ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function failures() 
    {
        $failures = $this->get_failures_current_year();
        $priority = $this->get_failures_by_priority();
        $status = $this->get_failures_by_status();

        return array(
            "failures" => $failures,
            "priority" => $priority,
            "status" => $status
        );
    }


    public function count_payments_periods()
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(pago_id) AS total, periodo_id FROM pagos 
            WHERE YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE)
            GROUP BY periodo_id
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function count_payments_months()
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(pago_id) AS total, MONTH(pago_fecha_captura) AS month FROM pagos 
            WHERE YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE)
            GROUP BY month ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_balance_boxes()
    {
        $query = Flight::gnconn()->prepare("SELECT * FROM cajas");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_count_by_status() 
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(pagos.pago_id) AS total, pagos_status.pago_status_nombre FROM pagos
            INNER JOIN pagos_status
            ON pagos.status_pago = pagos_status.pago_status
            WHERE YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE)
            GROUP BY pagos_status.pago_status_nombre ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_wait_balance()
    {
        $query = Flight::gnconn()->prepare("
            SELECT SUM(mensualidad) AS waiting FROM clientes
            INNER JOIN clientes_servicios
            ON clientes.cliente_id = clientes_servicios.cliente_id
            WHERE clientes_servicios.cliente_paquete != 0
            AND clientes_servicios.cliente_status IN (1,2,4,5,6)
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_register_balance($period)
    {
        $query = Flight::gnconn()->prepare("
            SELECT SUM(pago_monto - pago_descuento) AS registered FROM pagos 
            WHERE periodo_id = ?
        ");
        $query->execute([ $period ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * finance
     *
     * @return array
     */
    public function finance(): array
    {
        $period = $this->get_to_period();
        $months_payment = $this->count_payments_months();
        $registered = $this->get_register_balance($period[0]);
        $payments = $this->count_payments_periods();
        $status = $this->get_count_by_status();
        $balance = $this->get_balance_boxes();
        $waiting = $this->get_wait_balance();

        $data = [ 
            "payments" => $payments, 
            "months_payment" => $months_payment, 
            "balance" => $balance,
            "waiting" => $waiting,
            "status" => $status,
            "registered" => $registered
        ];
        
        return $data;
    }


    


    public function group_payments()
    {
        $SQL = "SELECT periodo_id, COUNT(pago_id) AS total FROM pagos WHERE YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE) AND status_pago != 0 GROUP BY periodo_id ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * payment_decline
     *
     * @return array
     */
    public function payment_decline()
    {
        $periodo = $this->get_before_periods()[ 0 ];
        $SQL = "SELECT periodo_id, COUNT(pago_id) AS total FROM pagos WHERE periodo_id = '$periodo'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * get_total_reports
     * 
     * @return array
     */
    public function get_total_reports()
    {
        $SQL = "SELECT MONTH(fecha_captura) AS mes, COUNT(reporte_id) AS total FROM `reportes_fallas` WHERE YEAR(fecha_captura) = YEAR(CURRENT_DATE) GROUP BY MONTH(fecha_captura) ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_dashboard_home
     *
     * @return array
     */
    public function get_dashboard_home()
    {
        return array(
            "payments" => array(
                "autorizacion" => $this->get_payments_autorization(),
                "current_payments" => $this->get_payments_current(),
                "wait_payments" => $this->get_payments_waiting(),
                "group_payments" => $this->group_payments(),
                "last_period" => $this->payment_decline(),
            ),
            "failures" => $this->get_report_failures(),
            "count_failures" => $this->get_total_reports(),
            "mikrotiks" => $this->get_all_mikrotiks(),
            "colognes" => $this->get_all_colognes(),
            "status" => $this->status_customers(),
            "customers" => array(
                $this->get_customers_by_status(1)[ 0 ],
                $this->get_customers_by_status(2)[ 0 ],
                $this->get_customers_by_status(3)[ 0 ],
                $this->get_customers_by_status(4)[ 0 ],
                $this->get_customers_by_status(5)[ 0 ],
                $this->get_customers_by_status(6)[ 0 ],
                $this->get_count_all_customers()[ 0 ],
                $this->get_count_all_activos()[ 0 ]
            )
        );
    }

    public function get_payments_autorization()
    {
        $SQL = "SELECT COUNT(pago_id) AS total FROM pagos WHERE status_pago = 2";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $rows[0]["name"] = "Autorizacion";
        $rows[0][ "color" ] = "orange";
        return $rows;
    }

    public function get_payments_current()
    {
        $periodo = $this->get_to_period()[0];
        $SQL = "SELECT COUNT(pago_id) AS total FROM pagos WHERE periodo_id = ?";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $periodo ]);
        $rows = $query->fetchAll();
        $rows[0]["name"] = "Actuales";
        $rows[0]["color"] = "pink";
        return $rows;
    }

    public function get_payments_waiting()
    {
        $SQL = "SELECT COUNT(cliente_id) AS total FROM clientes_servicios WHERE cliente_status IN (1,5,4) AND cliente_status != 3 AND cliente_status != 0 AND cliente_paquete != 0";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $rows[ 0 ][ "name" ] = "Esperados";
        $rows[ 0 ][ "color" ] = "skyblue";
        return $rows;
    }

    public function get_count_all_activos()
    {
        $SQL = "SELECT COUNT(*) AS total FROM clientes_servicios WHERE cliente_status != 0 AND cliente_status != 3 AND cliente_status != 2";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $rows[ 0 ][ "nombre_status" ] = "Activos";
        $rows[ 0 ][ "status_id" ] = 7;
        return $rows;
    }

    public function get_count_all_customers()
    {
        $SQL = "SELECT COUNT(*) AS total FROM clientes_servicios WHERE cliente_status != 0 AND cliente_status != 3";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $rows[ 0 ][ "nombre_status" ] = "Totales";
        $rows[ 0 ][ "status_id" ] = 8;
        return $rows;
    }

    public function get_customers_by_status($status)
    {
        $SQL = "SELECT COUNT(*) AS total, clientes_status.status_id, clientes_status.nombre_status FROM clientes_servicios INNER JOIN clientes_status ON clientes_servicios.cliente_status = clientes_status.status_id WHERE clientes_servicios.cliente_status = $status";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_all_mikrotiks          Get list mikrotiks data
     *
     * @return array              array of mikrotiks list
     **/
    public function get_all_mikrotiks()
    {
        $SQL = "SELECT * FROM mikrotiks WHERE mikrotik_status = 1";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    // Get list status customers //
    public function status_customers()
    {
        $SQL = "SELECT * FROM clientes_status WHERE status_id != 0";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        // Asignando a activos
        $rows[ count($rows) ][ "status_id" ] = 7;
        $rows[ count($rows) - 1 ][ "nombre_status" ] = "Activos";
        $rows[ count($rows) - 1 ][ "status_color" ] = "green";
        // Asignando a totales
        $rows[ count($rows) ][ "status_id" ] = 8;
        $rows[ count($rows) - 1 ][ "nombre_status" ] = "Totales";
        $rows[ count($rows) - 1 ][ "status_color" ] = "primary";
        return $rows;
    }


    // Get list cologne actives //
    public function get_all_colognes()
    {
        $SQL = "SELECT COUNT(colonia_id) AS total, colonia_status FROM colonias GROUP BY colonia_status";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    // obtener el presupuesto del mes //
    public function budget()
    {
        $periodo_id = date('Ym');
        $SQL = "SELECT * FROM presupuesto WHERE presupuesto_status = 1";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    // obtener los reportes agrupados por meses //
    public function get_report_failures()
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, CONCAT(empleados.empleado_nombre, ' ', empleados.empleado_apellido) AS empleado, reportes_fallas.* FROM reportes_fallas INNER JOIN clientes ON clientes.cliente_id = reportes_fallas.cliente_id INNER JOIN empleados ON reportes_fallas.usuario_captura = empleados.usuario_id WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE()) AND status = 1";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    // obtener los pagos no autorizados //
    public function payments_in_revision()
    {
        $SQL = "SELECT * FROM pagos WHERE status_pago = 2";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_payments_to_month()
    {
        $SQL = "SELECT * FROM pagos WHERE status_pago = 1 AND MONTH(pago_fecha_captura) = MONTH(CURRENT_DATE())";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function payments_waited_for_fifteen()
    {
        $SQL = "SELECT mensualidad FROM clientes_servicios WHERE cliente_corte = 1 AND cliente_status != 0 AND cliente_status != 3 AND cliente_status != 6";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * get_to_period
     *
     * @return array
     */
    public function get_to_period()
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


    public function payments_waited_for_thirty()
    {
        $SQL = "SELECT mensualidad FROM clientes_servicios WHERE cliente_corte = 2 AND cliente_status != 0 AND cliente_status != 3 AND cliente_status != 6";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

}

