<?php

class ReadMethods
{

    public function __construct()
    {}
    
    /**
     * get_all_methods
     *
     * @return array
     */
    public function get_all_methods() : array
    {
        $SQL = "SELECT * FROM metodos_admin ORDER BY metodo_id ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

}