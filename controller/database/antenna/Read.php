<?php


class ReadAntennas
{

    public function __construct()
    {
    }

    public function get_all_antennas()
    {
        $SQL = "SELECT * FROM `antenas` ORDER BY modelo ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

}