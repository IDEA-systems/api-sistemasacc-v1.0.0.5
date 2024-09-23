<?php

class Packages
{

    public $idpaquete;
    public $nombre_paquete;
    public $ancho_banda;
    public $cola_padre;
    public $precio;
    public $error;
    public $error_message;

    public function __construct($request = [])
    {
        $this->idpaquete = isset($request->idpaquete) ? $request->idpaquete : null;
        $this->nombre_paquete = isset($request->nombre_paquete) ? $request->nombre_paquete : null;
        $this->ancho_banda = isset($request->ancho_banda) ? $request->ancho_banda : null;
        $this->precio = isset($request->precio) ? $request->precio : null;
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

    public function exists_package()
    {
        $sql = "SELECT COUNT(idpaquete) AS `repeat` FROM paquetes WHERE nombre_paquete = '$this->nombre_paquete'";
        $query = Flight::gnconn()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows[ 0 ][ "repeat" ] > 0;
    }

    public function exists_package_edit()
    {
        $query = Flight::gnconn()->prepare("
            SELECT COUNT(idpaquete) AS `repeat` 
            FROM paquetes 
            WHERE nombre_paquete = ? 
            AND idpaquete != ?
        ");
        $query->execute([
            $this->nombre_paquete,
            $this->idpaquete
        ]);
        $rows = $query->fetchAll();
        return $rows[0]["repeat"] > 0;
    }
    
    /**
     * getNewId
     *
     * @return mixed
     */
    public function getNewId()
    {
        return Flight::gnconn()->lastInsertId();
    }

    public function create_package()
    {
        if ($this->exists_package()) {
            $this->error = true;
            $this->error_message = "El paquete ya existe!";
            return false;
        }

        $this->insert_into_paquetes();
    }


    public function insert_into_paquetes() : void 
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO paquetes 
                VALUES (null, ?, ?, 1, ?)
            ");
            $query->execute([ 
                $this->nombre_paquete,
                $this->ancho_banda,
                $this->precio
            ]);
            $this->idpaquete = $this->getNewId();
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar paquete!";
        }
    }



    public function get_package_by_id() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM paquetes
            WHERE idpaquete = ?
        ");
        $query->execute([ $this->idpaquete ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_all_packages() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM paquetes
            ORDER BY nombre_paquete ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function update_package()
    {
        if ($this->exists_package_edit()) {
            $this->error = true;
            $this->error_message = "El paquete ya existe!";
            return false;
        }

        try {
            $query = Flight::gnconn()->prepare("
                UPDATE `paquetes` 
                SET `nombre_paquete` = ?,
                `ancho_banda`= ?,
                `precio`= ?
                WHERE idpaquete = ?
            ");
            $query->execute([
                $this->nombre_paquete,
                $this->ancho_banda,
                $this->precio,
                $this->idpaquete
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar paquete!";
        }
    }

    public function verify_customer()
    {
        $sql = "SELECT cliente_id FROM clientes_servicios WHERE cliente_paquete = $this->idpaquete";
        $query = Flight::gnconn()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        return !empty($rows);
    }

    public function delete_package($idpaquete)
    {
        $this->idpaquete = $idpaquete;
        if ($this->verify_customer() || $idpaquete == 0) {
            $this->error = true;
            $this->error_message = "Existen clientes con el paquete actual!";
            return false;
        }

        try {
            $query = Flight::gnconn()->prepare("
                DELETE FROM paquetes 
                WHERE idpaquete = ?
            ");
            $query->execute([ 
                $this->idpaquete 
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al eliminar el paquete";
        }
    }

}