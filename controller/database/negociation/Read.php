<?php

class ReadNegociation {

  public $negociation;
  public $cliente_id;

  public function __construct($request = []) {
    $this->cliente_id = isset($request->cliente_id) ? $request->cliente_id : null;
  }

  public function get_client_negociation($cliente_id) {
    if (is_null($this->cliente_id)) {
      $this->cliente_id = $cliente_id;
    }

    $SQL = "SELECT * FROM negociaciones WHERE cliente_id = '$this->cliente_id' AND status_negociacion = 1";
    $query = Flight::gnconn()->prepare($SQL);
    $query->execute();
    $rows = $query->fetchAll();
    return $rows;
  }

  public function get_negociation_to_month($cliente_id) {
    if (is_null($this->cliente_id)) {
      $this->cliente_id = $cliente_id;
    }

    $SQL = "SELECT * FROM negociaciones WHERE cliente_id = '$this->cliente_id' AND MONTH(fecha_inicio) = MONTH(CURRENT_DATE()) AND YEAR(CURRENT_DATE()) = YEAR(fecha_inicio)";
    $query = Flight::gnconn()->prepare($SQL);
    $query->execute();
    $rows = $query->fetchAll();
    return $rows;
  }


  public function get_payments_anterity($cliente_id) {
    if (is_null($this->cliente_id)) {
      $this->cliente_id = $cliente_id;
    }

    $periods = $this->get_to_periods();
    $text = json_encode($periods);
    $order = array('"','[',']');
    $replace = array("'","(",")");
    $IN = str_replace($order, $replace, $text);

    $SQL = "SELECT pago_id, periodo_id, cliente_id FROM pagos WHERE cliente_id = '$this->cliente_id' AND periodo_id IN $IN AND YEAR(pago_fecha_captura) = YEAR(CURRENT_DATE())";
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

  public function min_max_date_negociation() {
      $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE(), INTERVAL 20 DAY)) AS max_date, DATE(DATE_ADD(CURRENT_DATE(), INTERVAL 5 DAY)) AS min_date";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
  }

}