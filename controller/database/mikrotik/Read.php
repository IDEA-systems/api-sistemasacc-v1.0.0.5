<?php

class ReadMikrotik
{

    public $mikrotiks;
    public $method_admins;


    public function __construct()
    {
    }


    public function get_all_mikrotiks() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM mikrotiks
            ORDER BY mikrotik_nombre ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }
    
    /**
     * verify_mikrotiks_connection_api
     *
     * @return array
     */
    public function verify_mikrotiks_connection_api() : array
    {
        $mikrotiks = $this->get_all_mikrotiks();
        for ($i = 0; $i < count($mikrotiks); $i++) {
            $conn = new Mikrotik(
                $mikrotiks[$i]["mikrotik_ip"],
                $mikrotiks[$i]["mikrotik_usuario"],
                $mikrotiks[$i]["mikrotik_password"],
                $mikrotiks[$i]["mikrotik_puerto"]
            );
            if ($conn->connected) {
                $mikrotiks[$i]["connect"] = true;
            } else {
                $mikrotiks[$i]["connect"] = false;
            }
        }
        return $mikrotiks;
    }
    
    /**
     * get_all_mikrotiks_db
     *
     * @return array
     */
    public function get_all_mikrotiks_db() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                mikrotik_nombre, 
                mikrotik_ip, 
                mikrotik_mac, 
                mikrotik_id,
                mikrotik_dominio,
                mikrotik_status
            FROM mikrotiks 
            ORDER BY mikrotik_status 
            DESC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_mikrotik_by_id($mikrotik_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM mikrotiks 
            WHERE mikrotik_id = ?
        ");
        $query->execute([ $mikrotik_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

}


?>