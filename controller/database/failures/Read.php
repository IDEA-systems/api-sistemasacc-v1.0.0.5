<?php

  /*********************
   * Failures Class
   * 
   * Class for data read MySQL
   * All reports
   * Report by id
   * Report customer
   * Reports end
   * Reports active
   * Reports for month
   * Report for date
   * 
  ************************/
  class ReadFailures {

    private $user_id;       // Usuario
    private $search;      // Parametros de busqueda
    private $date;       // Fecha del reporte
    private $status;      // Status del reporte
    private $priority;     // Prioridad del reporte 
    private $cliente_id;       // Detalles del reporte de un cliente
    private $report_id;       // ID para los detalles del reporte
    public $all;      // Save reports fails
    public $finalizados;
    public $revision;
    public $cancelados;
    public $urgentes;


    public function __construct() {
      $this->all = $this->get_report_fails();
      $this->finalizados = $this->fails_finished();
      $this->revision = $this->fails_revision();
      $this->cancelados = $this->fails_canceled();
      $this->urgentes = $this->fails_urgent();
    }


    public function __get($propiedad) {
      if (property_exists($this, $propiedad)) {
        return $this->$propiedad;
      }
    }

    public function __set($propiedad, $valor) {
      if (property_exists($this, $propiedad)) {
        $this->$propiedad = $valor;
      }
    }


    
    /**
     * get_report_fails
     *
     * Obtener los reportes
     * @return void
     * 
    **/
    public function get_report_fails() {
      $SQL = "SELECT * FROM clientes INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id INNER JOIN reportes_fallas ON clientes.cliente_id = reportes_fallas.cliente_id INNER JOIN usuarios ON reportes_fallas.usuario_control = usuarios.usuario_id CROSS JOIN empleados ON usuarios.usuario_id = empleados.usuario_id WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE()) ";

      if (isset($this->cliente_id)) {
        $SQL .= "AND cliente_id = '$this->cliente_id'";
      }

      if (isset($this->user_id)) {
        $SQL .= "AND usuario_captura = '$this->user_id'";
      }

      if (isset($this->status)) {
        $SQL .= "AND status = '$this->status'";
      }

      if (isset($this->report_id)) {
        $SQL .= "AND reporte_id = '$this->report_id'";
      }

      if (isset($this->priority)) {
        $SQL .= "AND prioridad = '$this->priority'";
      }

      if (isset($this->search)) {
        $SQL .= "AND MATCH(clientes.cliente_nombres, clientes.cliente_apellidos) AGAINST('$this->search') OR clientes.cliente_nombres LIKE '%$this->search%' OR clientes.cliente_apellidos LIKE '%$this->search%'";
      }

      $SQL .= " ORDER BY status ASC LIMIT 500";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }


    
    /**
     * 
     * fails_finished
     * 
     * Obtener reportes finalizados
     * @return void
     * 
    **/
    public function fails_finished() {
      $SQL = "SELECT * FROM clientes INNER JOIN reportes_fallas ON clientes.cliente_id = reportes_fallas.cliente_id INNER JOIN usuarios ON reportes_fallas.usuario_control = usuarios.usuario_id CROSS JOIN empleados ON usuarios.usuario_id = empleados.usuario_id WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE()) AND status = 2 ";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      return $query->fetchAll();
    }


    
    /**
     * fails_revision
     * 
     * Obtener reportes en revision
     * @return void
     * 
    **/
    public function fails_revision() {
      $SQL = "SELECT * FROM clientes INNER JOIN reportes_fallas ON clientes.cliente_id = reportes_fallas.cliente_id INNER JOIN usuarios ON reportes_fallas.usuario_control = usuarios.usuario_id CROSS JOIN empleados ON usuarios.usuario_id = empleados.usuario_id WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE()) AND status = 1";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      return $query->fetchAll();
    }
    
    
    
    /**
     * fails_canceled
     * 
     * Obtener reportes cancelados
     * @return void
     * 
    **/
    public function fails_canceled() {
      $SQL = "SELECT * FROM clientes INNER JOIN reportes_fallas ON clientes.cliente_id = reportes_fallas.cliente_id INNER JOIN usuarios ON reportes_fallas.usuario_control = usuarios.usuario_id CROSS JOIN empleados ON usuarios.usuario_id = empleados.usuario_id WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE()) AND status = 0";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      return $query->fetchAll();
    }

    public function fails_urgent() {
      $SQL = "SELECT * FROM clientes INNER JOIN reportes_fallas ON clientes.cliente_id = reportes_fallas.cliente_id INNER JOIN usuarios ON reportes_fallas.usuario_control = usuarios.usuario_id CROSS JOIN empleados ON usuarios.usuario_id = empleados.usuario_id WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE()) AND prioridad = 1 AND status = 1";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      return $query->fetchAll();
    }


        
    /**
     * customerFail
     *
     * @param  mixed $cliente_id
     * @return void
     * Get fails by cliente_id
     *
    **/
    public function customerFail() {
      $SQL = "SELECT * FROM reportes_fallas WHERE cliente_id = '$this->cliente_id' AND status = 1 AND YEAR(fecha_captura) = YEAR(CURRENT_DATE())";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }


  }
