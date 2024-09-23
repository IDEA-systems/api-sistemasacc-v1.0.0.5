<?php

  class ReadEmployees {
    
    public $getall;


    public function __construct() {
      $this->getall = $this->get_all_employees();
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
     * get_all_employs
     *
     * @return array
     * Return list of employs
     * 
     */
    public function get_all_employees() {
      $query = Flight::gnconn()->prepare("
        SELECT * FROM empleados 
        CROSS JOIN usuarios 
        ON empleados.usuario_id = usuarios.usuario_id 
        WHERE usuarios.usuario_status != 3
      ");
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }


    public function get_all_promotors() {
      $query = Flight::gnconn()->prepare("
        SELECT * FROM promotores 
        WHERE status = 'activo'
      ");
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

  }
  

?>