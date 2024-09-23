<?php


class Antenna
{

    public $marca;                // Marca del antena
    public $modelo;               // Modelo del antena
    public $idantenna;              // {ID} del antena
    public $fotoantena;            // Foto del antena
    public $error_message;        // Detalles del error
    public $conflict;                // true o false
    public $error;                // true o false


    public function __construct($request = [], $files = [])
    {
        $this->idantenna = isset($request->idantenna) ? $request->idantenna : null;
        $this->modelo = isset($request->modelo) ? $request->modelo : null;
        $this->marca = isset($request->marca) ? $request->marca : null;

        $type = isset($files->fotoantena['type']) 
            && !empty($files->fotoantena['type']) 
            && !is_null($files->fotoantena['type'])
                ? $files->fotoantena['type'] 
                : null;

        $format = !is_null($type) ? explode("/", $type)[1] : null;
        $new_name = !is_null($format) ? "$this->modelo.$format" : null;

        $this->fotoantena = !is_null($new_name)
            ? $new_name
            : null;
    }

    
    /**
     * search_repeat_antenna
     *
     * @return array
     */
    public function search_repeat_antenna() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT idantena 
            FROM antenas 
            WHERE modelo = ? 
            AND idantena != ?
        ");
        $query->execute([
            $this->modelo,
            $this->idantenna
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * create
     *
     * @return void
     */
    public function create() : void
    {
        $antennas = $this->search_repeat_antenna();
        if (count($antennas) >= 1) {
            $this->conflict = true;
            $this->error_message = "La antena ya existe!";
            return;
        }
        $this->insert_into_antennas();
    }

    
    /**
     * insert_into_antennas
     *
     * @return void
     */
    public function insert_into_antennas() : void 
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO antenas 
                VALUES (null, ?, ?, ?, 1)
            ");
            $query->execute([
                $this->modelo,
                $this->marca,
                $this->fotoantena
            ]);
            $this->idantenna = $this->last_id();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Error al agregar antenna!";
        }
    }
    
    /**
     * update
     *
     * @return void
     */
    public function update() : void
    {
        $antennas = $this->search_repeat_antenna();
        if (count($antennas) >= 1) {
            $this->conflict = true;
            $this->error_message = "La antena ya existe!";
            return;
        }
        $this->update_in_antennas();
    }



    public function update_in_antennas() : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE antenas 
                SET modelo = ?, 
                marca = ?, 
                fotoantena = ? 
                WHERE idantena = ?
            ");
            $query->execute([
                $this->modelo,
                $this->marca,
                $this->fotoantena,
                $this->idantenna
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar antenna!";
        }
    }

    
    /**
     * get_antenna_by_id
     *
     * @return array
     */
    public function get_antenna_by_id() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM antenas 
            WHERE idantena = ?
        ");
        $query->execute([ $this->idantenna ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * get_all_antennas
     *
     * @return array
     */
    public function get_all_antennas() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM `antenas` 
            ORDER BY modelo ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function save_img($files)
    {
        // Si no existen datos de archivos
        if (is_null($this->fotoantena)) return true;

        $path = './assets/images/antenas/';
        $tmp_name = isset($files->fotoantena['tmp_name']) 
            ? $files->fotoantena['tmp_name'] 
            : null;

        return move_uploaded_file($tmp_name, $path.$this->fotoantena);
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