<?php
  
  class ReadFinance {
		public $balance_in_boxes;
		public $expected_balance;
		public $payment_month;
		public $departures_month;
		public $post_month;
		public $balance_period;
		public $balance_year;
		public $budgets;
		public $balance_month;
		
		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			$this->balance_in_boxes = $this->balance_in_boxes();
			$this->expected_balance = $this->expected_balance();
			$this->payment_month = $this->payment_month();
			$this->departures_month = $this->departures_month();
			$this->post_month = $this->post_month();
			$this->balance_period = $this->balance_period();
			$this->balance_year = $this->balance_year();
			$this->budgets = $this->budgets();
			$this->balance_month = $this->balance_month();
		}
		
		/**
		 * balance_in_boxes
		 *
		 * @return array
		 */
		public function balance_in_boxes() {
			$SQL = "SELECT saldo_actual, nombre FROM cajas";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
		
		/**
		 * expected_balance
		 *
		 * @return array
		 */
		public function expected_balance() {
			$SQL = "SELECT SUM(mensualidad) AS expected_balance, COUNT(clientes.cliente_id) AS total_payments FROM clientes INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id WHERE clientes_servicios.cliente_paquete != 0 AND clientes_servicios.cliente_status != 3 AND clientes_servicios.cliente_status != 0";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
		
		/**
		 * payment_month
		 *
		 * @return array
		 */
		public function payment_month() {
			$SQL = "SELECT SUM(pago_monto) AS payment_month, COUNT(pago_id) AS total_payment FROM pagos WHERE MONTH(pago_fecha_captura) = MONTH(CURRENT_DATE()) AND YEAR(CURRENT_DATE()) = YEAR(pago_fecha_captura)";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
		
		/**
		 * departures_month
		 *
		 * @return array
		 */
		public function departures_month() {
			$SQL = "SELECT SUM(monto_movimiento) AS departures_month FROM movimientos_cajas WHERE tipo_movimiento = 2 AND MONTH(fecha_movimiento) = MONTH(CURRENT_DATE()) AND YEAR(fecha_movimiento) = YEAR(CURRENT_DATE())";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
		
		/**
		 * post_month
		 *
		 * @return array
		 */
		public function post_month() {
			$SQL = "SELECT SUM(monto_movimiento) AS departures_month FROM movimientos_cajas WHERE tipo_movimiento = 1 AND MONTH(fecha_movimiento) = MONTH(CURRENT_DATE()) AND YEAR(fecha_movimiento) = YEAR(CURRENT_DATE())";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
		
		/**
		 * balance_period 			Balanc total por periodos
		 *
		 * @return array
		 */
		public function balance_period() {
			$SQL = "SELECT periodos.periodo_id, SUM(pagos.pago_monto) AS total FROM periodos INNER JOIN pagos ON periodos.periodo_id = pagos.periodo_id WHERE YEAR(pagos.pago_fecha_captura) = YEAR(CURRENT_DATE()) AND pagos.status_pago != 0 GROUP BY periodos.periodo_id";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}

		/**
		 * balance_year      Balance del año
		 *
		 * @return array
		 */
		public function balance_year() {
			$SQL = "SELECT SUM(pago_monto) AS total, COUNT(pago_id) AS pagos FROM pagos WHERE YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE()) AND status_pago = 1";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}

			
		/**
		 * budgets 					Presupuesto del mes
		 *
		 * @return array 		Retorna los datos del presupuesto
		 */
		public function budgets() {
			$SQL = "SELECT * FROM presupuesto WHERE presupuesto_status = 1 AND MONTH(fecha_inicio) = MONTH(CURRENT_DATE())";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
		
		/**
		 * balance_month 					Balance del mes
		 *
		 * @return array
		 */
		public function balance_month() {
			$SQL = "SELECT SUM(monto_movimiento) AS total, COUNT(movimiento_id) AS entries FROM movimientos_cajas WHERE status_movimiento = 1 AND MONTH(fecha_movimiento) = MONTH(CURRENT_DATE())";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}

  }

?>