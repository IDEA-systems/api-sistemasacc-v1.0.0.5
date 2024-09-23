<?php

class Brand
{

    public $error;
    public $error_message;
    private $name;
    private $code_country;
    private $phone;
    private $email;
    private $website;
    private $address;
    private $rfc;
    private $postal_code;
    private $description;
    private $welcome;
    private $alertas;
    private $suspension;
    private $negociacion;
    private $facturacion;
    private $pago_capturado;
    private $negociacion_terminada;
    private $reportes_fallas;
    private $brand;

    public function __construct($request = [], $files = [])
    {
        $this->name = isset($request->name) 
            ? $request->name 
            : null;

        $this->code_country = isset($request->code_country) 
            ? $request->code_country 
            : null;

        $this->phone = isset($request->phone) 
            ? $request->phone 
            : null;

        $this->rfc = isset($request->rfc) 
            ? $request->rfc 
            : null;

        $this->postal_code = isset($request->postal_code) 
            ? $request->postal_code 
            : null;

        $this->email = isset($request->email) 
            ? $request->email 
            : null;

        $this->website = isset($request->website) 
            ? $request->website 
            : null;

        $this->address = isset($request->address) 
            ? $request->address 
            : null;

        $this->description = isset($request->description) 
            ? $request->description 
            : null;

        $this->welcome = isset($request->welcome) 
            ? $request->welcome 
            : null;

        $this->alertas = isset($request->alertas) 
            ? $request->alertas 
            : null;

        $this->suspension = isset($request->suspension) 
            ? $request->suspension 
            : null;

        $this->negociacion = isset($request->negociacion) 
            ? $request->negociacion 
            : null;

        $this->facturacion = isset($request->facturacion) 
            ? $request->facturacion 
            : null;

        $this->pago_capturado = isset($request->pago_capturado) 
            ? $request->pago_capturado 
            : null;

        $this->negociacion_terminada = isset($request->negociacion_terminada) 
            ? $request->negociacion_terminada 
            : null;

        $this->reportes_fallas = isset($request->reporte_falla) 
            ? $request->reporte_falla 
            : null;
        
        $type = isset($files->brand['type']) 
            && !empty($files->brand['type']) 
            && !is_null($files->brand['type'])
                ? $files->brand['type'] 
                : null;

        $format = !is_null($type) 
            ? explode("/", $type)[1] 
            : null;

        $new_name = !is_null($format) 
            ? "brand.$format" 
            : null;

        $this->brand = !is_null($new_name)
            ? $new_name
            : null;
    }

    public function save_img_brand($files = [])
    {
        // Si no existen datos de archivos
        if (
            !isset($files->brand['name']) || 
            empty($files->brand['name'])
        ) return true;

        $path = './assets/images/enterprice/';
        $tmp_name = isset($files->brand['tmp_name']) 
            ? $files->brand['tmp_name'] 
            : null;

        return move_uploaded_file($tmp_name, $path.$this->brand);
    }

    public function enable_config($id)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE configuration 
                SET status = 1 
                WHERE id = ?
            ");
            $query->execute([ $id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }


    public function update_messages()
    {
        $this->update_message_welcome();
        $this->update_message_alerts();
        $this->update_message_suspension();
        $this->update_message_negociation();
        $this->update_message_billing();
        $this->update_message_payment_captured();
        $this->update_message_negociation_finish();
        $this->update_message_fail_report();
    }

    public function update_message_negociation_finish()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ? 
                WHERE name_id = 'negociacion_terminada'
            ");
            $query->execute([ $this->negociacion_terminada ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }



    public function update_message_welcome()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ? 
                WHERE name_id = 'welcome'
            ");
            $query->execute([ $this->welcome ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }




    public function update_message_alerts()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ? 
                WHERE name_id = 'alertas'
            ");
            $query->execute([ $this->alertas ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }



    public function update_message_suspension()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ? 
                WHERE name_id = 'suspension'
            ");
            $query->execute([
                $this->suspension
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }



    public function update_message_negociation()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ? 
                WHERE name_id = 'negociacion'
            ");
            $query->execute([ $this->negociacion ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }

    

    public function update_message_billing()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ? 
                WHERE name_id = 'facturacion'
            ");
            $query->execute([ $this->facturacion ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }



    public function update_message_payment_captured()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ?
                WHERE name_id = 'pago_capturado'
            ");
            $query->execute([ $this->pago_capturado ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }




    public function update_message_fail_report()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE mensajeria 
                SET template = ?
                WHERE name_id = 'reporte_falla'
            ");
            $query->execute([ $this->reportes_fallas ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }


    

    public function disable_config($id)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE configuration 
                SET status = 0 
                WHERE id = ?
            ");
            $query->execute([ $id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrió un error!";
        }
    }

    public function get_information_brand() {
        $SQL = "SELECT * FROM brand_information";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_messages() {
        $SQL = "SELECT * FROM mensajeria";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_config() {
        $SQL = "SELECT * FROM configuration";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function save_enterprice_info()
    {
        try {
            $query = Flight::gnconn()->prepare("
          INSERT INTO brand_information 
          VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
            $query->execute([ 
                $this->name,
                $this->code_country,
                $this->phone,
                $this->address,
                $this->postal_code,
                $this->rfc,
                $this->website,
                $this->email,
                $this->description
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar los datos de la empresa!";
        }
    }

    public function update_enterprice_info()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE brand_information 
                SET `name`= ?,
                `codigo_pais` = ?,
                `phone`= ?, 
                `address`= ?, 
                `postal_code`= ?, 
                `rfc`= ?, 
                `website`= ?, 
                `email`= ?, 
                `description`= ?
                WHERE 1
            ");
            $query->execute([ 
                $this->name,
                $this->code_country,
                $this->phone,
                $this->address,
                $this->postal_code,
                $this->rfc,
                $this->website,
                $this->email,
                $this->description
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar los datos!";
        }
    }

}