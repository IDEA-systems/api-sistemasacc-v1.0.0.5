<?php

class Status
{

    public function __construct() 
    {}
    
    /**
     * get_all_status
     *
     * @return array
     */
    public function get_all_status() {
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

    public function status_equipos() {
        $SQL = "SELECT * FROM status_equipo";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }
    
    public function status_installations() {
        $SQL = "SELECT * FROM status_instalaciones";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }
}