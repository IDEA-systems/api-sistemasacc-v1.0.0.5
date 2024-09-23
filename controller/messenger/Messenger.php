<?php

class Messenger
{
    public $brand;
    public $templates;
    public $data;
    public $whatsapp;
    public $email;

    public function __construct() {}
    
    /**
     * get_brand
     * Obtener la informacion la empresa
     *
     * @return array
    */
    public function get_brand() {
        $SQL = "SELECT * FROM brand_information";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * get_templates
     *
     * @return array
     */
    public function get_templates($name) {
        $SQL = "
            SELECT * FROM mensajeria 
            WHERE name_id = ?
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $name ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * whatsapp
     * Enviar notificacion de whatsapp
     * @param  mixed $data
     * @return mixed
     */
    public function whatsapp($data, $client_id, $type_message) {
        $file = isset($data["mediaUrl"]) ? $data["mediaUrl"] : null;
        $this->brand = $this->get_brand();
        $curl = curl_init();
        curl_setopt_array($curl, 
            array(
                CURLOPT_URL => $this->brand[0]["whatsapp_api"] . "/lead",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            )
        );

        $response = curl_exec($curl);
        $array = json_decode($response, true);
        $this->whatsapp = isset($array["status"]) ? $array["status"] : false;
        curl_close($curl);

        // Verificar estatus del mensaje enviado
        if ($this->whatsapp != "PENDING") {
            $this->customer_fail_message($data, $client_id, $type_message, $file);
            $this->whatsapp = false;
            return $this->whatsapp;
        }
        
        $this->whatsapp = true;
        return $this->whatsapp;
    }

    public function Email($email, $subject, $message, $key) {
        $this->brand = $this->get_brand();
        $curl = curl_init();
        curl_setopt_array($curl, 
            array(
                CURLOPT_URL => $this->brand[0]["email_api"] . "/email",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    "email" => $email,
                    "subject" => $subject,
                    "message" => $message,
                    "key" => $key
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                    // 'Authorization: Bearer ' . $this->brand[0]["resend_key"]
                ),
            )
        );

        $this->email = curl_exec($curl);
        curl_close($curl);
    }

    /**
     * customer_fail_message
     *
     * @param  mixed $message
     * @param  mixed $cliente_id
     * @param  mixed $type_message
     * @return void
    */
    public function customer_fail_message($message, $cliente_id, $type_message, $file)
    {
        $exists = $this->exists_message_from_client_id($cliente_id, $type_message);
        if (!$exists) {
            $this->insert_into_message_retry($message, $cliente_id, $type_message, $file);
        } else {
            $this->update_message_retry($message, $cliente_id, $type_message, $file);
        }
    }

    /**
     * insert_into_message_retry
     *
     * @param  mixed $message
     * @return void
     */
    public function insert_into_message_retry($message, $cliente_id, $type_message, $file)
    {
        $msg = $message["message"];
        $phone = $message["phone"];
        $SQL = "
            INSERT INTO message_retry 
            VALUES (null, ?,?,?,?,?, CURRENT_TIMESTAMP)
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([
            $cliente_id, 
            $type_message, 
            $phone, 
            $msg,
            $file
        ]);
    }


    public function update_message_retry($message, $cliente_id, $type_message, $file)
    {
        $msg = $message["message"];
        $phone = $message["phone"];
        $SQL = "
            UPDATE message_retry 
            SET phone = ?,
            message = ?,
            file_url = ?
            WHERE cliente_id = ?
            AND type_message = ?
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([
            $phone,
            $msg,
            $file,
            $cliente_id,
            $type_message
        ]);
    }

    
    /**
     * exists_message_from_client_id
     *
     * @param  mixed $cliente_id
     * @param  mixed $type_message
     * @return bool
     */
    public function exists_message_from_client_id($cliente_id, $type_message)
    {
        $SQL = "
            SELECT message_id 
            FROM message_retry 
            WHERE cliente_id = ? 
            AND type_message = ?
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([
            $cliente_id,
            $type_message
        ]);
        $rows = $query->fetchAll();
        $is_exists = empty($rows);
        if ($is_exists) return false;
        return true;
    }

}