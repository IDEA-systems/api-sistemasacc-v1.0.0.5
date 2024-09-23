<?php

class Login
{
    public $error;
    private $usuario_id;
    private $user_name;
    public $userData;
    public $error_message;
    private $password;

    /**
     * __construct
     * Construir el objeto
     * @param  mixed $request
     * @return void
     **/
    public function __construct($request = [])
    {
        $this->usuario_id = isset($request->usuario_id) ? $request->usuario_id : null;
        $this->user_name = isset($request->user_name) ? $request->user_name : null;
        $this->password = isset($request->user_password) ? $request->user_password : null;
    }

    /**
     * __get
     * Obtener un valor del objeto
     * @param  mixed $prop
     * @return void
     **/
    public function __get($prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }
    }


    /**
     * __set
     * Agregar un valor a una propiedad
     * @param  mixed $prop
     * @param  mixed $value
     * @return void
     **/
    public function __set($prop, $value)
    {
        if (property_exists($this, $prop)) {
            $this->$prop = $value;
        }
    }

    public function delete($usuario_id)
    {
        $this->usuario_id = $usuario_id;
        $this->session_abort();
    }


    public function session_abort()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE usuarios 
                SET usuario_status = 1
                WHERE usuario_id = ?
            ");
            $query->execute([ $this->usuario_id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al finalizar la session";
        }
    }

    public function get_permission($usuario_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT usuarios_tipos.* FROM usuarios_tipos 
            INNER JOIN usuarios 
            ON usuarios_tipos.tipo_id = usuarios.usuario_tipo 
            WHERE usuario_id = ?
        ");
        $query->execute([ $usuario_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function search_user()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                empleados.*,
                usuarios.*,
                usuarios_tipos.*,
                usuarios_status.*
            FROM usuarios
            INNER JOIN empleados
            ON usuarios.usuario_id = empleados.usuario_id 
            INNER JOIN usuarios_tipos
            ON usuarios.usuario_tipo = usuarios_tipos.tipo_id
            INNER JOIN usuarios_status
            ON usuarios.usuario_status = usuarios_status.status_id
            WHERE usuarios.usuario_nombre = ?
        ");
        $query->execute([ $this->user_name ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function is_root($usuario_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT usuario_tipo FROM usuarios 
            WHERE usuario_id = ?
        ");
        $query->execute([ $usuario_id ]);
        $rows = $query->fetchAll();
        return $rows[0]["usuario_tipo"] == 1;
    }

    public function is_user($usuario_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT usuario_id FROM usuarios 
            WHERE usuario_id = ?
            AND usuario_status != 2 
            AND usuario_status != 3
        ");
        $query->execute([ $usuario_id ]);
        $rows = $query->fetchAll();
        return !empty($rows);
    }

    /**
     * password_verify
     * Valida el password 
     * @param  mixed $rows
     * @return bool
     **/
    public function password_verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
}