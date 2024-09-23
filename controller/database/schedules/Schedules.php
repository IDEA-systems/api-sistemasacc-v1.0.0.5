<?php

class Schedules {

    public function __construct()
    {}

    
    /**
     * get_schedules_installation
     *
     * @return array
     */
    public function get_schedules_installation() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM horarios
            WHERE tipo_horario = ?
        ");
        $query->execute(['instalaciones']);
        $rows = $query->fetchAll();
        return $rows;
    }

}