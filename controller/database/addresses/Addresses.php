<?php

class addresses
{
    public function __construct() 
    {}

    public function get_all_addresses()
    {
        $SQL = "SELECT id, address, name FROM addresses ORDER BY name ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

}