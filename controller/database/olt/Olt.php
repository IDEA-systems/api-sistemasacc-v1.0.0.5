<?php

class Olt
{
    public $error;
    public $conflict;
    public $error_message;
    public $id;
    public $nombre;
    public $modelo;
    public $serie;
    public $address;
    public $mikrotik;
    public function __construct($request = [])
    {
        $this->id = isset($request->id) && !empty($request->id) ? $request->id : null;
        $this->nombre = isset($request->nombre) ? $request->nombre : null;
        $this->modelo = isset($request->modelo) ? $request->modelo : null;
        $this->serie = isset($request->serie) ? $request->serie : null;
        $this->address = isset($request->address) ? $request->address : null;
        $this->mikrotik = isset($request->mikrotik) ? $request->mikrotik : null;
    }

    public function get_all_olt()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM olts 
            ORDER BY nombre ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_olt_by_id()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM olts
            WHERE id = ?
        ");
        $query->execute([ $this->id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function create()
    {
        $search_address = $this->search_address();
        $search_serie = $this->search_serie();
        $search_name = $this->search_name();

        if (!empty($search_address)) {
            $this->conflict = true;
            $this->error_message = "La dirección IPv4 ya existe!";
        } 

        else if ($search_serie) {
            $this->conflict = true;
            $this->error_message = "El número de serie ya existe!";
        }

        else if ($search_name) {
            $this->conflict = true;
            $this->error_message = "El nombre de olt ya existe!";
        }
        
        else {
            $this->insert_into_olts();
        }
    }

    public function insert_into_olts()
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO olts 
                VALUES (NULL, ?, ?, ?, ?, ?)
            ");
            $query->execute([
                $this->nombre,
                $this->modelo,
                $this->serie,
                $this->address,
                $this->mikrotik
            ]);
            $this->id = $this->lastId();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar la olt";
        }
    }

    public function search_name() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM olts 
            WHERE nombre = ?
        ");
        $query->execute([ $this->nombre ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function search_serie() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM olts 
            WHERE serie = ?
        ");
        $query->execute([ $this->serie ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function search_address() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM olts 
            WHERE address = ?
        ");
        $query->execute([ $this->address ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function lastId() {
        return Flight::gnconn()->lastInsertId();
    }
}