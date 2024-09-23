<?php

class Equipment
{

    public $id;
    public $name;
    public $model;
    public $brand;
    public $image;
    public $conflict;
    public $error;
    public $error_message;


    public function __construct($request = [], $files =[])
    {
        $this->name = isset($request->name) ? $request->name : null;
        $this->model = isset($request->model) ? $request->model : null;
        $this->brand = isset($request->brand) ? $request->brand : null;
        $this->image = isset ($files->image->name) ? $files->image->name : '';
    }

    
    /**
     * exist_in_database
     *
     * @param  mixed $model
     * @return bool
     */
    public function exist_in_database($model) : bool
    {
        // Asignar el valor
        $this->model = $model;

        $query = Flight::gnconn()->prepare("
            SELECT id 
            FROM equipos 
            WHERE model = ?
        ");
        $query->execute([ $this->model ]);
        $rows = $query->fetchAll();
        $is_registered = count($rows) >= 1;
        return $is_registered;
    }
    

    /**
     * create_new_equipment
     *
     * @return void
     */
    public function create_new_equipment() : void
    {
        $validate = $this->exist_in_database($this->model);
        if ($validate) {
            $this->conflict = true;
            $this->error_message = "El equipo ya existe";
        }

        if (!$validate) {
            $this->insert_into_equipos();
        }
    }

    
    /**
     * insert_into_equipos
     *
     * @return void
     */
    public function insert_into_equipos() : void
    {
        try {

            $query = Flight::gnconn()->prepare("
                INSERT INTO equipos 
                VALUES (null, ?, ?, ?, ?)
            ");

            $query->execute([
                $this->name,
                $this->model,
                $this->brand,
                $this->image
            ]);
            
            $this->id = $this->get_new_id();

        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "No se pudo agregar el equipo!";
        }
    }



    public function get_all_equipment()
    {
        $SQL = "SELECT * FROM equipos ORDER BY id ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_new_id()
    {
        return Flight::gnconn()->lastInsertId();
    }
}