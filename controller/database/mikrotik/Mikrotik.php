<?php

class Mikrotiks
{

    public $mikrotik_id;
    public $mikrotik_nombre;
    public $mikrotik_ip;
    public $mikrotik_mac;
    public $mikrotik_usuario;
    public $mikrotik_password;
    public $mikrotik_puerto;
    public $parent_queue;
    public $mikrotik_dominio;
    public $mikrotik_status;
    public $conflict;
    public $error;
    public $rules;
    public $accept;
    public $redirect;
    public $drop;
    public $error_message;


    public function __construct($request = [])
    {
        $this->mikrotik_id = isset($request->mikrotik_id) ? $request->mikrotik_id : null;
        $this->mikrotik_nombre = isset($request->mikrotik_nombre) ? $request->mikrotik_nombre : null;
        $this->mikrotik_ip = isset($request->mikrotik_ip) ? $request->mikrotik_ip : null;
        $this->mikrotik_usuario = isset($request->mikrotik_usuario) ? $request->mikrotik_usuario : "admin";
        $this->mikrotik_password = isset($request->mikrotik_password) ? $request->mikrotik_password : null;
        $this->mikrotik_puerto = isset($request->mikrotik_puerto) ? $request->mikrotik_puerto : 8728;
        $this->parent_queue = isset($request->parent_queue) ? $request->parent_queue : "none";
        $this->mikrotik_mac = isset($request->mikrotik_mac) && !empty($request->mikrotik_mac) ? $request->mikrotik_mac : null;
        $this->mikrotik_dominio = isset($request->mikrotik_dominio) && !empty($request->mikrotik_dominio) ? $request->mikrotik_dominio : null;
        $this->mikrotik_status = isset($request->mikrotik_status) ? $request->mikrotik_status : 1;
    }

    public function __get($propiedad)
    {
        if (property_exists($this, $propiedad)) {
            return $this->$propiedad;
        }
    }

    public function __set($propiedad, $valor)
    {
        if (property_exists($this, $propiedad)) {
            $this->$propiedad = $valor;
        }
    }


    
    /**
     * save_mikrotik
     *
     * @return void
     */
    public function create() : void
    {
        $name = $this->search_name();
        $ip = $this->search_ip();
        $mac = $this->search_mac();
        $domain = $this->search_domain();

        if (count($name) >= 1) {
            $this->conflict = true;
            $msg = "El nombre ya se encuentra registrado!";
            $this->error_message = $msg;
            return;
        }
        
        else if (count($ip) >= 1) {
            $this->conflict = true;
            $msg = "IPv4 ya se encuentra registrada!";
            $this->error_message = $msg;
            return;
        } 
        
        else if (count($mac) >= 1) {
            $this->conflict = true;
            $msg = "La mac ya se encuentra registrada!";
            $this->error_message = $msg;
            return;
        } 
        
        else if (count($domain) >= 1) {
            $this->conflict = true;
            $msg = "El dominio ya se encuentra registrado!";
            $this->error_message = $msg;
            return;
        } 

        else {
            $this->insert_into_mikrotik();
            $this->add_configuration();
        }
    }


    public function change_password()
    {
        $rows = $this->get_by_id_all_data();
        if ($rows[0]['mikrotik_password'] == $this->mikrotik_password) {
            $this->error = true;
            $this->error_message = "La contraseña es la misma!";
        }
        
        if ($rows[0]['mikrotik_password'] != $this->mikrotik_password) {
            $this->update_password();
        }
    }


    public function update_password() {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mikrotiks 
                SET mikrotik_password = ?
                WHERE mikrotik_id = ?
            ");
            $query->execute([
                $this->mikrotik_password,
                $this->mikrotik_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error al cambiar el password!";
        }
    }

    public function retry_add_config()
    {
        $rows = $this->get_by_id_all_data();
        $this->mikrotik_ip = $rows[0]["mikrotik_ip"];
        $this->mikrotik_usuario = $rows[0]["mikrotik_usuario"];
        $this->mikrotik_password = $rows[0]["mikrotik_password"];
        $this->mikrotik_puerto = $rows[0]["mikrotik_puerto"];
        $this->add_configuration();
    }



    public function add_configuration()
    {
        $conn = new Mikrotik(
            $this->mikrotik_ip,
            $this->mikrotik_usuario,
            $this->mikrotik_password,
            $this->mikrotik_puerto
        );

        if (!$conn->connected) {
            $this->accept = false;
            $this->drop = false;
            $this->redirect = false;
            return;
        }
        
        if ($conn->connected) {
            $this->add_filter_drop($conn);
            $this->add_filter_accept($conn);
            $this->add_nat_dstnat($conn);
        }
    }
    
    public function add_filter_drop($conn)
    {
        $this->drop = $conn->comm("/ip/firewall/filter/add", [
            "chain" => "forward",
            "src-address-list" => "MOROSOS",
            "action" => "drop"
        ]);
    }

    public function add_filter_accept($conn) {
        $this->accept = $conn->comm("/ip/firewall/filter/add", [
            "chain" => "forward",
            "protocol" => "udp",
            "dst-port" => "53",
            "src-address-list" => "MOROSOS",
            "action" => "accept"
        ]);
    }

    public function add_nat_dstnat($conn) {
        $this->redirect = $conn->comm("/ip/firewall/nat/add", [
            "chain" => "dst-nat",
            "protocol" => "tcp",
            "dst-port" => "80,443",
            "src-address-list" => "MOROSOS",
            "action" => "redirect",
            "to-ports" => "8080"
        ]);
    }
    
    /**
     * insert_into_mikrotik
     *
     * @return void
     */
    public function insert_into_mikrotik() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO mikrotiks 
                VALUES (null, TRIM(?), TRIM(?), TRIM(?), TRIM(?), TRIM(?), TRIM(?), TRIM(?), TRIM(?), ?)
            ");
            $query->execute([
                $this->mikrotik_nombre,
                $this->mikrotik_ip,
                $this->mikrotik_mac,
                $this->mikrotik_dominio,
                $this->mikrotik_password,
                $this->mikrotik_usuario,
                $this->mikrotik_puerto,
                $this->parent_queue,
                $this->mikrotik_status
            ]);
            $this->mikrotik_id = $this->lastId();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Ocurrió un error al agregar el mikrotik!";
        }
    }



    public function update()
    {
        $name = $this->search_name();
        $ip = $this->search_ip();
        $mac = $this->search_mac();
        $domain = $this->search_domain();
        
        if (count($name) >= 1) {
            $this->conflict = true;
            $msg = "El nombre ya se encuentra registrado!";
            $this->error_message = $msg;
            return;
        }
        
        else if (count($ip) >= 1) {
            $this->conflict = true;
            $msg = "IPv4 ya se encuentra registrada!";
            $this->error_message = $msg;
            return;
        } 
        
        else if (count($mac) >= 1) {
            $this->conflict = true;
            $msg = "La mac ya se encuentra registrada!";
            $this->error_message = $msg;
            return;
        } 
        
        else if (count($domain) >= 1) {
            $this->conflict = true;
            $msg = "El dominio ya se encuentra registrado!";
            $this->error_message = $msg;
            return;
        } 

        else {
            $this->update_mikrotik();
        }
    }


    
    /**
     * update_mikrotik
     *
     * @return void
     */
    public function update_mikrotik() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mikrotiks SET 
                `mikrotik_nombre` = TRIM(?), 
                `mikrotik_ip` = TRIM(?),
                `mikrotik_mac` = TRIM(?), 
                `mikrotik_dominio` = TRIM(?),
                `mikrotik_usuario` = TRIM(?),
                `mikrotik_puerto` = ?,
                `mikrotik_status` = ?
                WHERE mikrotik_id = ?
            ");
            $query->execute([
                $this->mikrotik_nombre,
                $this->mikrotik_ip,
                $this->mikrotik_mac,
                $this->mikrotik_dominio,
                $this->mikrotik_usuario,
                $this->mikrotik_puerto,
                $this->mikrotik_status,
                $this->mikrotik_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error al agregar el mikrotik!";
        }
    }


    public function search_name()
    {
        $query = Flight::gnconn()->prepare("
            SELECT mikrotik_id 
            FROM mikrotiks 
            WHERE TRIM(LOWER(mikrotik_nombre)) = TRIM(LOWER(?)) 
            AND mikrotik_id != ?
        ");
        $query->execute([
            $this->mikrotik_nombre,
            $this->mikrotik_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function search_ip()
    {
        $query = Flight::gnconn()->prepare("
            SELECT mikrotik_id 
            FROM mikrotiks 
            WHERE TRIM(mikrotik_ip) = TRIM(?)
            AND mikrotik_id != ?
        ");
        $query->execute([
            $this->mikrotik_ip,
            $this->mikrotik_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function search_mac()
    {
        $query = Flight::gnconn()->prepare("
            SELECT mikrotik_id 
            FROM mikrotiks 
            WHERE TRIM(mikrotik_mac) = TRIM(?)
            AND mikrotik_id != ?
        ");
        $query->execute([
            $this->mikrotik_mac,
            $this->mikrotik_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function search_domain()
    {
        $query = Flight::gnconn()->prepare("
            SELECT mikrotik_id 
            FROM mikrotiks 
            WHERE LOWER(TRIM(mikrotik_dominio)) = LOWER(TRIM(?))
            AND mikrotik_id != ?
        ");
        $query->execute([
            $this->mikrotik_dominio,
            $this->mikrotik_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_by_id_all_data()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM mikrotiks 
            WHERE mikrotik_id = ?
        ");
        $query->execute([ $this->mikrotik_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_mikrotik_by_id()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                mikrotik_id, 
                mikrotik_nombre, 
                mikrotik_ip, 
                mikrotik_mac, 
                mikrotik_usuario, 
                mikrotik_dominio, 
                mikrotik_puerto, 
                mikrotik_status 
            FROM mikrotiks 
            WHERE mikrotik_id = ?
        ");
        $query->execute([ $this->mikrotik_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_all_mikrotiks()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                mikrotik_id, 
                mikrotik_nombre, 
                mikrotik_ip, 
                mikrotik_mac, 
                mikrotik_usuario, 
                mikrotik_dominio, 
                mikrotik_puerto, 
                mikrotik_status 
            FROM mikrotiks
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    
    /**
     * all_mikrotik_data
     * Get all data mikrotiks
     * @return array
     */
    public function all_mikrotik_data() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM mikrotiks
            WHERE mikrotik_status = 1
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
    public function mikrotik_api_status() : array
    {
        $mikrotiks = $this->all_mikrotik_data();
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
     * lastId
     *
     * @return mixed
     */
    public function lastId() 
    {
        return Flight::gnconn()->lastInsertId();
    }
}
