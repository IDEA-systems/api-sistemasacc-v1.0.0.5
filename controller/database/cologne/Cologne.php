<?php

class Cologne
{

    public $nombre_colonia;
    public $mikrotik_control;
    public $status_colonia;
    public $colonia_id;
    public $error_message;
    public $error;
    public $conflict;


    public function __construct($request = [])
    {
        $this->colonia_id = isset($request->colonia_id) ? $request->colonia_id : null;
        $this->nombre_colonia = isset($request->nombre_colonia) ? $request->nombre_colonia : null;
        $this->mikrotik_control = isset($request->mikrotik_control) ? $request->mikrotik_control : null;
        $this->status_colonia = isset($request->status_colonia) ? $request->status_colonia : 1;
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



    public function get_all_colognes() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT colonias.*, mikrotiks.mikrotik_nombre 
            FROM colonias 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            AND mikrotiks.mikrotik_status = 1 
            ORDER BY colonias.nombre_colonia ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * search_repeat_cologne
     *
     * @param string $nombre_colonia
     * @return array
     */
    public function search_repeat_cologne()
    {
        $query = Flight::gnconn()->prepare("
            SELECT nombre_colonia 
            FROM colonias 
            WHERE nombre_colonia = ?
            AND colonia_id != ?
        ");
        $query->execute([ 
            $this->nombre_colonia,
            $this->colonia_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function Create()
    {
        // Buscar el nombre de la colonia
        $repeat = $this->search_repeat_cologne();
        if (count($repeat) >= 1) {
            $this->conflict = true;
            $this->error_message = "El nombre de la colonia ya existe!";
            return;
        }
        $this->insert_in_colonias();
    }


    public function insert_in_colonias()
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO colonias 
                VALUES (null, ?, null, ?, ?, null, null)
            ");
            $query->execute([ 
                $this->nombre_colonia,
                $this->mikrotik_control,
                $this->status_colonia
            ]);
            $this->colonia_id = $this->last_id();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar la colonia!";
        }
    }
    
    /**
     * update
     *
     * @return void
     */
    public function update() : void
    {
        $repeat = $this->search_repeat_cologne();
        if (count($repeat) >= 1) {
            $this->conflict = true;
            $this->error_message = "El nombre de la colonia ya existe!";
            return;
        }
        $this->update_in_colonias();
    }
    
    /**
     * update_in_colonias
     *
     * @return void
     */
    public function update_in_colonias() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE colonias 
                SET `nombre_colonia`= ?,
                `mikrotik_control`= ? 
                WHERE colonia_id = ?
            ");
            $query->execute([
                $this->nombre_colonia,
                $this->mikrotik_control,
                $this->colonia_id
            ]);
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error al actualizar la colonia!";
        }
    }

    public function delete($colonia_id)  : void
    {
        $this->colonia_id = $colonia_id;
        $clients_registered = $this->search_clients_registered();
        
        if (count($clients_registered) >= 1) {
            $this->conflict = true;
            $this->error_message = "Existen clientes en esta colonia!";
        }

        if (count($clients_registered) == 0) {
            $this->disabled_cologne();
        }
    }


    public function disabled_cologne() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE colonias 
                SET `colonia_status` = 0 
                WHERE colonia_id = ?
            ");
            $query->execute([ $this->colonia_id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }


    public function search_clients_registered() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT cliente_id 
            FROM clientes_servicios 
            WHERE colonia = ? 
            AND cliente_status != 3
        ");
        $query->execute([ $this->colonia_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * enable
     *
     * @param  mixed $colonia_id
     * @return void
     */
    public function enable($colonia_id) : void
    {
        $this->colonia_id = $colonia_id;
        $this->enable_cologne();
    }

    
    /**
     * enable_cologne
     *
     * @return void
     */
    public function enable_cologne() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE colonias 
                SET `colonia_status` = 1 
                WHERE colonia_id = ?
            ");
            $query->execute([ $this->colonia_id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error al habilitar la colonia!";
        }
    }

    
    /**
     * get_cologne_by_id
     *
     * @return array
     */
    public function get_cologne_by_id() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT colonias.*, mikrotiks.mikrotik_nombre 
            FROM colonias 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            WHERE colonia_id = ?
        ");
        $query->execute([ $this->colonia_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }
    
    /**
     * last_id
     *
     * @return mixed
     */
    public function last_id()
    {
        return Flight::gnconn()->lastInsertId();
    }
}