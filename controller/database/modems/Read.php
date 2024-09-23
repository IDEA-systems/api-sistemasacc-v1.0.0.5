<?php

class ReadModems
{

    public function __construct()
    {}

    public function get_all_modems()
    {
        $SQL = "SELECT * FROM modem";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

}