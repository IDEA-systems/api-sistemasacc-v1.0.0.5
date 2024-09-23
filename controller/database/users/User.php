<?php

class User
{

    public $usuario_id;
    public $usuario_nombre;
    public $usuario_password;
    public $usuario_status;
    public $usuario_tipo;
    public $usuario_perfil;
    public $img_company;
    public $fecha_ingreso;
    public $empleado_id;
    public $empleado_email;
    public $empleado_telefono;
    public $empleado_nombre;
    public $empleado_apellido;
    public $empleado_curp;
    public $empleado_nss;
    public $empleado_rfc;
    public $empleado_edad;
    public $empleado_fecha;
    public $empleado_nacimiento;
    public $empleado_sueldo;
    public $empleado_tpago;
    public $empleado_ine;
    public $empleado_targeta;
    public $empleado_area;


    public $error;
    public $error_message;
    public $conflict;


    public function __construct($request = [], $files = [])
    {
        // User id generate
        
        $this->usuario_id = isset($request->usuario_id) && 
            !empty($request->usuario_id) 
                ? $request->usuario_id 
                : uniqid();

        $this->usuario_nombre = isset($request->usuario_nombre) 
            ? $request->usuario_nombre 
            : null;

        $this->usuario_password = isset($request->usuario_password) 
            ? password_hash($request
            ->usuario_password, PASSWORD_DEFAULT, [ 'cost' => 15 ]) : null;

        $this->usuario_status = isset($request->usuario_status) 
            ? $request->usuario_status 
            : 1;

        $this->usuario_tipo = isset($request->usuario_tipo) 
            ? $request->usuario_tipo 
            : 2;

        $this->img_company = isset($request->img_company) 
            ? $request->img_company 
            : null;

        $this->fecha_ingreso = isset($request->fecha_ingreso) 
            ? $request->fecha_ingreso 
            : null;

        $this->empleado_id = isset($request->empleado_id) 
            ? $request->empleado_id 
            : uniqid();

        $this->empleado_email = isset($request->empleado_email) 
            ? $request->empleado_email 
            : null;

        $this->empleado_telefono = isset($request->empleado_telefono) 
            ? $request->empleado_telefono 
            : null;

        $this->empleado_nombre = isset($request->empleado_nombre) 
            ? $request->empleado_nombre 
            : null;

        $this->empleado_apellido = isset($request->empleado_apellido) 
            ? $request->empleado_apellido 
            : null;

        $this->empleado_curp = isset($request->empleado_curp) 
            ? $request->empleado_curp 
            : null;

        $this->empleado_nss = isset($request->empleado_nss) 
            ? $request->empleado_nss 
            : null;

        $this->empleado_rfc = isset($request->empleado_rfc) 
            ? $request->empleado_rfc 
            : null;

        $this->empleado_edad = isset($request->empleado_edad) 
            ? $request->empleado_edad 
            : null;

        $this->empleado_fecha = isset($request->empleado_fecha) 
            ? $request->empleado_fecha 
            : null;

        $this->empleado_nacimiento = isset($request->empleado_nacimiento) 
            ? $request->empleado_nacimiento 
            : null;

        $this->empleado_sueldo = isset($request->empleado_sueldo) 
            ? $request->empleado_sueldo 
            : null;

        $this->empleado_tpago = isset($request->empleado_tpago) 
            ? $request->empleado_tpago 
            : null;

        $this->empleado_targeta = isset($request->empleado_targeta) 
            ? $request->empleado_targeta 
            : null;

        $this->empleado_area = isset($request->empleado_area) 
            ? $request->empleado_area 
            : null;
            
        $type_ine = isset($files->empleado_ine['type']) 
            && !empty($files->empleado_ine['type']) 
            && !is_null($files->empleado_ine['type'])
                ? $files->empleado_ine['type'] 
                : null;

        $format_ine = !is_null($type_ine) ? explode("/", $type_ine)[1] : null;
        $name_ine = !is_null($format_ine) ? "$this->empleado_id.$format_ine" : null;

        $this->empleado_ine = !is_null($name_ine)
            ? $name_ine
            : null;

        $type_profile = isset($files->usuario_perfil['type']) 
            && !empty($files->usuario_perfil['type']) 
            && !is_null($files->usuario_perfil['type'])
                ? $files->usuario_perfil['type'] 
                : null;

        $format_profile = !is_null($type_profile) ? explode("/", $type_profile)[1] : null;
        $name_profile = !is_null($format_profile) ? "$this->usuario_id.$format_profile" : null;

        $this->usuario_perfil = !is_null($name_profile)
            ? $name_profile
            : null;
    }
    

    public function get_user_by_id()
    {
        $query = Flight::gnconn()->prepare("
            SELECT empleados.*, usuarios.*, usuarios_tipos.* FROM usuarios 
            LEFT JOIN empleados 
            ON usuarios.usuario_id = empleados.usuario_id 
            LEFT JOIN usuarios_tipos 
            ON usuarios.usuario_tipo = usuarios_tipos.tipo_id 
            LEFT JOIN usuarios_status 
            ON usuarios.usuario_status = usuarios_status.status_id 
            WHERE usuarios.usuario_id = ?
        ");
        $query->execute([ $this->usuario_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function user_name_repeat()
    {
        $query = Flight::gnconn()->prepare("
            SELECT usuario_id 
            FROM usuarios 
            WHERE usuario_nombre = ? 
            AND usuario_id != ?
        ");
        $query->execute([
            $this->usuario_nombre,
            $this->usuario_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function empleado_email_repeat()
    {
        $query = Flight::gnconn()->prepare("
            SELECT empleado_id FROM empleados 
            WHERE empleado_email = ? 
            AND empleado_id != ?
        ");
        $query->execute([
            $this->empleado_email,
            $this->empleado_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function empleado_telefono_repeat()
    {
        $query = Flight::gnconn()->prepare("
            SELECT empleado_id FROM empleados 
            WHERE empleado_telefono = ? 
            AND empleado_id != ?
        ");
        $query->execute([
            $this->empleado_telefono,
            $this->empleado_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function empleado_curp_repeat()
    {
        $query = Flight::gnconn()->prepare("
            SELECT empleado_id FROM empleados 
            WHERE empleado_curp = ? 
            AND empleado_id != ?
        ");
        $query->execute([
            $this->empleado_curp,
            $this->empleado_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function empleado_nss_repeat()
    {
        $query = Flight::gnconn()->prepare("
            SELECT empleado_id FROM empleados 
            WHERE empleado_nss = ? 
            AND empleado_id != ?
        ");
        $query->execute([
            $this->empleado_nss,
            $this->empleado_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function empleado_rfc_repeat()
    {
        $query = Flight::gnconn()->prepare("
            SELECT empleado_id FROM empleados 
            WHERE empleado_rfc = ? 
            AND empleado_id != ?
        ");
        $query->execute([
            $this->empleado_rfc,
            $this->empleado_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function create_user()
    {
        if (!empty($this->user_name_repeat())) {
            $this->error = true;
            $this->error_message = "El nombre de usuario ya existe!";
            return false;
        }
        if (!empty($this->empleado_email_repeat())) {
            $this->error = true;
            $this->error_message = "El email ya existe!";
            return false;
        }

        if (!empty($this->empleado_telefono_repeat())) {
            $this->error = true;
            $this->error_message = "El telefono ya existe!";
            return false;
        }

        if (!empty($this->empleado_curp_repeat())) {
            $this->error = true;
            $this->error_message = "La curp ya existe!";
            return false;
        }

        if (!empty($this->empleado_nss_repeat())) {
            $this->error = true;
            $this->error_message = "El seguro social ya existe!";
            return false;
        }

        if (!empty($this->empleado_rfc_repeat())) {
            $this->error = true;
            $this->error_message = "El rfc ya existe!";
            return false;
        }

        $insert = $this->save_user();
        if (!$insert) {
            $this->error = true;
            $this->error_message = "Error al agregar usuario!";
            return false;
        }

        $save_employee = $this->save_employee();
        if (!$save_employee) {
            $this->error = true;
            $this->error_message = "Ocurrieron al agregar el empleado!";
            $this->delete_user();
            return false;
        }
        return true;
    }


    public function delete_user()
    {
        try {
            $query = Flight::gnconn()->prepare("
                DELETE FROM usuario 
                WHERE usuario_id = ?
            ");
            $query->execute([ $this->usuario_id ]);
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar usuario";
        }
    }



    public function save_user()
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO usuarios 
                VALUES(?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $query->execute([
                $this->usuario_id,
                $this->usuario_nombre,
                $this->usuario_password,
                $this->usuario_status,
                $this->usuario_tipo,
                $this->usuario_perfil,
                date('Y-m-d:h:m:s')
            ]);
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar usuario";
            return false;
        }
    }


    public function save_employee()
    {
        try {
            $this->empleado_id = uniqid();
            $query = Flight::gnconn()->prepare("
                INSERT INTO empleados 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $query->execute([
                $this->empleado_id,
                $this->empleado_email,
                $this->empleado_telefono,
                $this->empleado_nombre,
                $this->empleado_apellido,
                $this->empleado_curp,
                $this->empleado_nss,
                $this->empleado_rfc,
                $this->empleado_edad,
                $this->empleado_fecha,
                $this->empleado_nacimiento,
                $this->empleado_sueldo,
                $this->empleado_tpago,
                $this->empleado_ine,
                $this->empleado_targeta,
                $this->empleado_area,
                $this->usuario_id
            ]);
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al guardar empleado";
            return false;
        }
    }


    public function update_user($files = [])
    {
        $name = $this->user_name_repeat();
        $email = $this->empleado_email_repeat();
        $telefono = $this->empleado_telefono_repeat();
        $curp = $this->empleado_curp_repeat();
        $seguro = $this->empleado_nss_repeat();
        $rfc = $this->empleado_rfc_repeat();

        if (!empty($name)) {
            $this->conflict = true;
            $this->error_message = "El nombre de usuario ya existe!";
            return false;
        } 
        
        else if (!empty($email)) {
            $this->conflict = true;
            $this->error_message = "El email ya existe!";
            return false;
        }

        else if (!empty($telefono)) {
            $this->conflict = true;
            $this->error_message = "El telefono ya existe!";
            return false;
        }

        else if (!empty($curp)) {
            $this->conflict = true;
            $this->error_message = "La curp ya existe!";
            return false;
        }

        else if (!empty($seguro)) {
            $this->conflict = true;
            $this->error_message = "El seguro social ya existe!";
            return false;
        }

        else if (!empty($rfc)) {
            $this->conflict = true;
            $this->error_message = "El rfc ya existe!";
            return false;
        } 
        
        else {
            $this->save_profile($files, $this->usuario_id);
            if ($this->error) return false;

            $this->save_ine($files, $this->empleado_id);
            if ($this->error) return false;

            $this->save_change_user();
            if ($this->error) return false;

            $this->save_change_employee();
            if ($this->error) return false;
        }
    }


    public function save_change_user()
    {
        try {
            $query = Flight::gnconn()->prepare( "
                UPDATE `usuarios` SET 
                `usuario_nombre`= ?,
                `usuario_perfil`= ? 
                WHERE usuario_id = ?
            ");
            $query->execute([
                $this->usuario_nombre,
                $this->usuario_perfil,
                $this->usuario_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar el usuario";
        }
    }


    public function save_change_employee()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE `empleados` SET 
                `empleado_email`= ?,
                `empleado_telefono`= ?,
                `empleado_nombre`= ?,
                `empleado_apellido`= ?,
                `empleado_curp`= ?,
                `empleado_nss`= ?,
                `empleado_rfc`= ?,
                `empleado_edad`= ?,
                `empleado_nacimiento`= ?,
                `empleado_sueldo`= ?,
                `empleado_tpago`= ?,
                `empleado_ine`= ?,
                `empleado_targeta`= ? 
                WHERE empleado_id =  ?
            ");
            $query->execute([
                $this->empleado_email,
                $this->empleado_telefono,
                $this->empleado_nombre,
                $this->empleado_apellido,
                $this->empleado_curp,
                $this->empleado_nss,
                $this->empleado_rfc,
                $this->empleado_edad,
                $this->empleado_nacimiento,
                $this->empleado_sueldo,
                $this->empleado_tpago,
                $this->empleado_ine,
                $this->empleado_targeta,
                $this->empleado_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar datos personales!";
        }
    }


    public function save_ine($files = [])
    {
        // Si no existen datos de archivos
        if (is_null($this->empleado_ine)) return true;

        $accept = ["jpg", "jpeg", "png", "pdf", "webp"];
        $type = explode("/", $files->empleado_ine['type'])[1];
        $path = "./assets/images/empleados/";
        $tmp_name = isset($files->empleado_ine['tmp_name']) 
            ? $files->empleado_ine['tmp_name'] 
            : null;
        
        if (!in_array($type, $accept)) {
            $this->error = true;
            $this->error_message = "La imagen no es correcta!";
            return false;
        }

        try {
            return move_uploaded_file($tmp_name, $path.$this->empleado_ine);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Error al mover la imagen!";
            return false;
        }
    }

    public function save_profile($files = [])
    {
        if (is_null($this->usuario_perfil)) return true;

        $accept = ["jpg", "jpeg", "png", "pdf", "webp"];
        $type = explode("/", $files->usuario_perfil['type'])[1];
        $path = "./assets/images/profiles/";
        $tmp_name = isset($files->usuario_perfil['tmp_name']) 
            ? $files->usuario_perfil['tmp_name'] 
            : null;
        
        if (!in_array($type, $accept)) {
            $this->error = true;
            $this->error_message = "La imagen no es correcta!";
            return false;
        }

        try {
            return move_uploaded_file($tmp_name, $path.$this->usuario_perfil);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Error al mover la imagen!";
            return false;
        }
        // $this->usuario_id = $usuario_id;
        // $is_file = isset($files->usuario_perfil) &&
        //     isset($files->usuario_perfil['name']) &&
        //     $files->usuario_perfil["name"] != "";

        // if (!$is_file) return true;

        // $type = explode("/", $files->usuario_perfil['type'])[1];
        // $tmp_name = $files->usuario_perfil['tmp_name'];
        // $path = "./assets/images/profiles/";
        // $newpath = $path."$this->usuario_id.$type";
        // $accept = ["jpg", "jpeg", "png", "pdf"];

        // if (!in_array($type, $accept)) {
        //     $this->error = true;
        //     $this->error_message = "La imagen no es correcta!";
        //     return false;
        // }

        // else {
        //     try {
        //         return move_uploaded_file($tmp_name, $newpath);
        //     } catch (Exception $error) {
        //         $this->error = true;
        //         $this->error_message = "Error al mover la imagen!";
        //         return false;
        //     }
        // }
    }

}