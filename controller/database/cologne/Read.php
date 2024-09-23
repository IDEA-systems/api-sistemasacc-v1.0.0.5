<?php

  class ReadCologne {

    public $total;
    public $enables;
    public $disables;
    public $customers;

    public function __construct() {
      $this->total = $this->total_cologne();
      $this->enables = $this->total_enables();
      $this->disables = $this->total_disables();
      $this->customers = $this->total_customers();
    }


    public function total_cologne() {
      $SQL = "SELECT COUNT(colonia_id) AS total FROM colonias";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }
    public function total_enables() {
      $SQL = "SELECT COUNT(colonia_id) AS total FROM colonias WHERE colonia_status = 1";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }
    public function total_disables() {
      $SQL = "SELECT COUNT(colonia_id) AS total FROM colonias WHERE colonia_status = 0";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }
    public function total_customers() {
      $SQL = "SELECT COUNT(clientes_servicios.cliente_id) AS total, colonias.nombre_colonia FROM colonias INNER JOIN clientes_servicios ON clientes_servicios.colonia = colonias.colonia_id WHERE colonias.colonia_status != 0 AND clientes_servicios.cliente_status != 3 AND clientes_servicios.cliente_status != 0 GROUP BY colonias.colonia_id ASC";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

    
    /**
     * get_all_colognes   Obtener la lista de colonias
     *
     * @return array
     */
    public function get_all_colognes() {
      $SQL = "
        SELECT 
          colonias.*, 
          mikrotiks.mikrotik_nombre 
        FROM colonias 
        INNER JOIN mikrotiks 
        ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
        AND mikrotiks.mikrotik_status = 1 
        ORDER BY colonias.nombre_colonia ASC
      ";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }


    public function get_cologne($colonia_id) {
      $SQL = "SELECT colonias.*, mikrotiks.mikrotik_id, mikrotiks.mikrotik_nombre FROM colonias INNER JOIN mikrotiks ON colonias.mikrotik_control = mikrotiks.mikrotik_id WHERE colonia_id = $colonia_id ORDER BY colonias.nombre_colonia";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

  }


?>