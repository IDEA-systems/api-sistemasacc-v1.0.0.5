<?php

class ReadCuts
{

    public function __construct()
    {}

    public function get_all_cuts()
    {
        $SQL = "SELECT * FROM cortes_servicio ORDER BY corte_id ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

}