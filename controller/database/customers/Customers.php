<?php

class Customer extends Messenger
{
    // Variables de formuñlarios
    private $cliente_id;
    private $cliente_telefono;
    private $cliente_email;
    private $serie_modem;
    private $cliente_mac;
    private $cliente_ip;
    private $servicio_id;
    private $cliente_nombres;
    private $cliente_olt;
    private $cliente_apellidos;
    private $cliente_telefono2;
    private $equipo_instalado;
    private $servicio_adicional;
    private $precio_instalacion;
    private $cliente_telegram;
    private $costo_servicio;
    private $cliente_domicilio;
    private $cliente_ine;
    private $cliente_rfc;
    private $cliente_razon_social;
    private $cliente_ap;
    private $status_equipo;
    private $status_equipo_instalado;
    private $cliente_instalacion;
    private $comentario;
    private $cliente_maps_ubicacion;
    private $metodo_bloqueo;
    private $mensualidad;
    private $costo_renta;
    private $cliente_tipo;
    private $cliente_paquete;
    private $cliente_status;
    private $cliente_corte;
    private $colonia;
    private $antena_instalada;
    private $modem_instalado;
    private $nombre_colonia;
    private $tipo_servicio;
    private $server;
    private $profiles;
    public $name_secret;
    public $password_secret;
    private $interface_arp;
    private $mikrotik_control;
    private $serie_equipo;
    private $ancho_banda;
    private $suspender;
    private $mikrotik_update;
    public $arp;
    public $leases;
    public $queues;
    public $pppoe;
    public $firewall;
    public $error;
    public $error_message;
    public $files;
    public $data;
    public $morosos;

    /**
     * __construct
     *
     * @param  mixed $request
     * @param  mixed $files
     * @return void
     */
    public function __construct($request = [], $files = [])
    {
        // Generar el id del cliente
        $this->files = $files;
        $this->data = $request;
        $this->cliente_status = $this->is_free_month() ? 6 : 1;

        $this->cliente_email = isset($request->cliente_email) 
            ? $request->cliente_email 
            : null;

        $this->cliente_telefono = isset($request->cliente_telefono) 
            ? $request->cliente_telefono 
            : null;

        $this->serie_modem = isset($request->serie_modem) 
            ? $request->serie_modem 
            : null;

        $this->cliente_ip = isset($request->cliente_ip) && 
            !empty($request->cliente_ip) 
                ? $request->cliente_ip 
                : null;

        $this->cliente_mac = isset($request->cliente_mac) && 
            !empty($request->cliente_mac) 
                ? $request->cliente_mac 
                : null;

        $this->cliente_nombres = isset($request->cliente_nombres) 
            ? trim($request->cliente_nombres) 
            : null;

        $this->cliente_apellidos = isset($request->cliente_apellidos) 
            ? trim($request->cliente_apellidos) 
            : null;

        $this->colonia = isset($request->colonia) 
            ? $request->colonia 
            : null;

        $this->nombre_colonia = isset($request->nombre_colonia) 
            ? $request->nombre_colonia 
            : null;

        $this->cliente_telefono2 = isset($request->cliente_tel_secundario) 
            ? $request->cliente_tel_secundario 
            : null;

        $this->serie_equipo = isset($request->serie_equipo) 
            ? $request->serie_equipo 
            : null;

        $this->cliente_telegram = isset($request->cliente_telegram) 
            ? $request->cliente_telegram 
            : null;

        $this->cliente_domicilio = isset($request->cliente_domicilio) 
            ? $request->cliente_domicilio 
            : null;

        $this->cliente_rfc = isset($request->cliente_rfc) 
            ? $request->cliente_rfc 
            : null;

        $this->cliente_razon_social = isset($request->cliente_razon_social) 
            ? $request->cliente_razon_social 
            : null;

        $this->cliente_olt = isset($request->cliente_olt) 
            ? $request->cliente_olt 
            : null;

        $this->tipo_servicio = isset($request->tipo_servicio) 
            ? $request->tipo_servicio : null;

        $is_wireless = $this->tipo_servicio == 1;

        $this->cliente_ap = $is_wireless && 
            isset($request->cliente_ap) 
                ? $request->cliente_ap 
                : $this->cliente_olt;

        $this->status_equipo = isset($request->status_equipo) 
            ? $request->status_equipo 
            : 1;

        $this->cliente_instalacion = isset($request->cliente_instalacion) 
            ? $request->cliente_instalacion 
            : date('Y-m-d');

        $this->comentario = isset($request->comentario) 
            ? $request->comentario 
            : null;

        $this->suspender = isset($request->suspender) 
            ? $request->suspender 
            : 0;

        $this->cliente_maps_ubicacion = isset($request->cliente_maps_ubicacion) 
            ? $request->cliente_maps_ubicacion 
            : null;

        $this->metodo_bloqueo = isset($request->metodo_bloqueo) 
            ? $request->metodo_bloqueo 
            : "DHCP";

        $this->mensualidad = isset($request->mensualidad) 
            ? $request->mensualidad 
            : 300;

        $this->costo_renta = isset($request->costo_renta) 
            ? $request->costo_renta 
            : 0;

        $this->cliente_tipo = isset($request->cliente_tipo) 
            ? $request->cliente_tipo 
            : 1;

        $this->cliente_paquete = isset($request->cliente_paquete) 
            ? $request->cliente_paquete 
            : 0;

        $this->cliente_corte = isset($request->cliente_corte) 
            ? $request->cliente_corte 
            : intval(date('d'));

        $this->antena_instalada = isset($request->antena_instalada) 
            ? $request->antena_instalada 
            : 0;

        $this->modem_instalado = isset($request->modem_instalado) 
            ? $request->modem_instalado 
            : 1;

        $this->mikrotik_control = isset($request->mikrotik_control) 
            ? $request->mikrotik_control 
            : null;

        $this->mikrotik_update = isset($request->mikrotik_update) 
            ? $request->mikrotik_update 
            : 0;

        $this->server = isset($request->servers) 
            ? $request->servers 
            : "all";

        $this->interface_arp = isset($request->interface_arp) 
            ? $request->interface_arp 
            : "ether1";

        $this->profiles = isset($request->profiles) 
            ? $request->profiles 
            : "default";

        $this->name_secret = isset($request->name_secret) 
            ? $request->name_secret 
            : "";

        $this->password_secret = isset($request->password_secret) 
            ? $request->password_secret 
            : "";

        $this->servicio_adicional = isset($request->adicionales) && $request->adicionales == 1;
        
        $this->equipo_instalado = isset($request->equipo_instalado) 
            ? $request->equipo_instalado 
            : 1;

        $this->status_equipo_instalado = isset($request->status_equipo_instalado) 
            ? $request->status_equipo_instalado 
            : 1;

        $this->servicio_id = isset($request->servicio_id) 
            ? $request->servicio_id 
            : 1;

        $this->costo_servicio = isset($request->costo_servicio) 
            ? $request->costo_servicio 
            : 0;

        $this->precio_instalacion = isset($request->precio_instalacion) 
            ? $request->precio_instalacion 
            : 0;

        $this->ancho_banda = isset($request->ancho_banda) 
            ? $request->ancho_banda 
            : "20M/20M";

        $this->cliente_id = isset($request->cliente_id) && 
            !empty($request->cliente_id) 
             ? $request->cliente_id 
             : $this->generate_customer_id();


        $type = isset($files->cliente_ine['type']) 
            && !empty($files->cliente_ine['type']) 
            && !is_null($files->cliente_ine['type'])
                ? $files->cliente_ine['type'] 
                : null;

        $format = !is_null($type) 
            ? explode("/", $type)[1] 
            : null;

        $new_name = !is_null($format) 
            ? "$this->cliente_id.$format" 
            : null;

        $this->cliente_ine = !is_null($new_name)
            ? $new_name
            : null;
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
     * insert_into_mikrotik_retry_set
     *
     * Actualizar los datos del cliente en el mikrotik
     * @param  array $prev_data  Datos que no se cambiaron del cliente
     * @return void
     */
    public function insert_into_mikrotik_retry_set($prev_data, $module)
    {
        $cliente_id = $prev_data[0]["cliente_id"];
        $prev_mikrotik = $prev_data[0]["mikrotik_id"];
        $prev_ip = $prev_data[0]["cliente_ip"];
        $prev_mac = $prev_data[0]["cliente_mac"];
        $prev_paquete = $prev_data[0]["cliente_paquete"];
        $prev_method = $prev_data[0]["metodo_bloqueo"];
        $prev_server = $prev_data[0]["server"];
        $prev_interface = $prev_data[0]["interface_arp"];
        $prev_profile = $prev_data[0]["profile"];
        $prev_user = $prev_data[0]["user_pppoe"];
        $query = Flight::gnconn()->prepare("
            REPLACE INTO mikrotik_retry_set 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)
        ");
        $query->execute([
            $cliente_id,
            $prev_ip,
            $prev_mac,
            $prev_paquete,
            $prev_mikrotik,
            $prev_method,
            $prev_server,
            $prev_interface,
            $prev_profile,
            $prev_user,
            $module,
        ]);
    }
    
        
    /**
     * insert_into_mikrotik_retry_remove
     *
     * @param  mixed $cliente_id
     * @param  mixed $mikrotik_id
     * @param  mixed $cliente_ip
     * @param  mixed $cliente_mac
     * @param  mixed $server
     * @param  mixed $interface_arp
     * @param  mixed $profile
     * @param  mixed $address_list
     * @param  mixed $module
     * @return void
     */
    public function insert_into_mikrotik_retry_remove($cliente_id, $mikrotik_id, $cliente_ip, $cliente_mac, $server, $interface_arp, $profile, $address_list, $module)
    {
        $query = Flight::gnconn()->prepare("
            INSERT INTO mikrotik_retry_remove 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, null)
        ");
        $query->execute([
            $cliente_id,
            $cliente_ip,
            $cliente_ip,
            $cliente_mac,
            $server,
            $interface_arp,
            $profile,
            $address_list,
            $module
        ]);
    }
    
    /**
     * customer_fail_mikrotik
     *
     * @param  mixed $cliente_id
     * @param  mixed $name
     * @return void
     */
    public function customer_fail_mikrotik($cliente_id, $mikrotik_id, $server, $interface_arp, $profile, $module)
    {
        $is_exists = $this->this_record_already_exists(
            'mikrotik_retry_add', 
            $cliente_id, $module
        );
        
        !$is_exists && $this->insert_into_mikrotik_retry_add(
            $cliente_id,
            $mikrotik_id,
            $server,
            $interface_arp,
            $profile,
            $module
        );
    }


    /**
     * insert_into_mikrotik_retry
     * 
     * Guardar los datos que no pudieron guardarse en el mikrotik para agregarlos despues
     * @param  mixed $cliente_id  Solo se necesita el id del cliente
     * @return void
     */
    public function insert_into_mikrotik_retry_add($cliente_id, $mikrotik_id, $server, $interface_arp, $profile, $module)
    {
        $query = Flight::gnconn()->prepare("
            INSERT INTO mikrotik_retry_add 
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $query->execute([
            $cliente_id,
            $mikrotik_id,
            $server, 
            $interface_arp, 
            $profile,
            $module
        ]);
    }

    
    /**
     * this_record_already_exists
     *
     * Verifica que no exista el registro
     * @param  mixed $TABLE
     * @param  mixed $cliente_id
     * @param  mixed $module
     * @return bool
     */
    public function this_record_already_exists($TABLE, $cliente_id, $module)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM $TABLE 
            WHERE cliente_id = ? 
            AND module = ?
        ");
        $query->execute([
            $cliente_id,
            $module
        ]);
        $rows = $query->fetchAll();
        $exists = empty($rows);
        if ($exists) return false;
        return true;
    }

    
    /**
     * customer_fail_update_mikrotik
     *
     * @param  mixed $prev_data
     * @return void
     */
    public function customer_fail_update_mikrotik($prev_data) 
    {
        $cliente_id = $prev_data[0]["cliente_id"];
        $is_exists = $this->this_record_already_exists('mikrotik_retry_set', $cliente_id, 'all');
        !$is_exists && $this->insert_into_mikrotik_retry_set($prev_data, 'all');
    }


    /**
     * is_free_month
     * Verificar si esta habilitada la opcion de mes gratis
     * @return number
     */
    public function is_free_month()
    {
        $SQL = "SELECT status FROM configuration WHERE id = 5";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $is_enable =  $rows[0]["status"] == 1;
        return $is_enable;
    }

    /**
     * 
     * Generar el ID del cliente
     * @param  mixed $cliente_nombres
     * @param  mixed $cliente_apellidos
     * @param  mixed $nombre_colonia
     * @return string
     **/
    public function generate_customer_id()
    {
        $brand = $this->get_brand();
        $enterpricename = substr($brand[0]["name"], 0, 4);
        $SQL = "SELECT COUNT(cliente_id) as total FROM clientes";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $total = $rows[0]['total'];

        if (
            !isset($this->cliente_nombres) ||
            !isset($this->cliente_apellidos) ||
            !isset($this->nombre_colonia)
        ) {
            $cliente_id = strtoupper(str_replace(" ", "", $enterpricename)) . date("hms") . $total;
            return $cliente_id;
        }

        $name = substr($this->cliente_nombres, 0, 2);
        $lastname = substr($this->cliente_apellidos, 0, 2);
        $colony = substr($this->nombre_colonia, 0, 2);
        $cliente_id = str_replace(" ", "", strtoupper(trim($enterpricename) . trim($name) . trim($lastname) . trim($colony) . $total));
        return $cliente_id;
    }



    /**
     *
     * @param  mixed $cliente_email
     * @return bool
     **/
    public function repeat_email()
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS names FROM `clientes` INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id WHERE clientes.cliente_email = '$this->cliente_email' AND clientes.cliente_id != '$this->cliente_id' AND clientes_servicios.cliente_status IN (1,2,4,5,6)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return count($rows) >= 1;
    }

    /**
     * 
     * Telefono
     * @param  mixed $cliente_telefono
     * @return bool
     **/
    public function repeat_phone()
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS names FROM `clientes` INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id WHERE clientes.cliente_telefono = '$this->cliente_telefono' AND clientes.cliente_id != '$this->cliente_id' AND clientes_servicios.cliente_status IN (1,2,4,5,6)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return count($rows) >= 1;
    }


    /**
     * MAC              Busca la mac ingresada
     * 
     * @param  mixed $cliente_mac
     * @return bool
     **/
    public function repeat_mac()
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS names FROM `clientes` INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id WHERE clientes.cliente_mac = '$this->cliente_mac' AND clientes.cliente_id != '$this->cliente_id' AND clientes_servicios.cliente_status IN (1,2,4,5,6)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return count($rows) >= 1;
    }


    /**
     * repeat_address                     Validar que no se repita la ip
     * 
     * @param  mixed $cliente_ip      Valida la ip del constructor
     * @param  mixed $cliente_id      Diferente de vacio o diferente al cliente_id
     * 
     * @return bool
     * 
     **/
    public function repeat_address()
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS names FROM `clientes` INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id WHERE clientes.cliente_ip = '$this->cliente_ip' AND clientes.cliente_id != '$this->cliente_id' AND clientes_servicios.cliente_status IN (1,2,4,5,6)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return count($rows) >= 1;
    }


    /**
     * repeat_serie_modem_equipo                     Validar que no se repita la ip
     * 
     * @param  mixed $cliente_iserie      Valida la ip del constructor
     * @param  mixed $cliente_id      Diferente de vacio o diferente al cliente_id
     * 
     * @return bool
     * 
     **/
    public function repeat_serie_modem_equipo()
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS names FROM `clientes` INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id INNER JOIN servicios_adicionales ON clientes.cliente_id = servicios_adicionales.cliente_id WHERE servicios_adicionales.serie_equipo = '$this->serie_modem' AND servicios_adicionales.serie_equipo != '' AND clientes.cliente_id != '$this->cliente_id' AND clientes_servicios.cliente_status IN (1,2,4,5,6)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return count($rows) >= 1;
    }


    /**
     * repeat_serie_modem
     *
     * @return bool
     */
    public function repeat_serie_modem()
    {
        $SQL = "SELECT CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS names FROM `clientes` INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id WHERE clientes.serie_modem = '$this->serie_modem' AND clientes.cliente_id != '$this->cliente_id' AND clientes_servicios.cliente_status IN (1,2,4,5,6)";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return count($rows) >= 1;
    }

    
    /**
     * send_welcome_whatsapp
     *
     * @return mixed
     */
    public function send_welcome_whatsapp() {
        $template = $this->get_templates('welcome');
        $brand = $this->get_brand();
        $phone = $this->cliente_telefono;
        $nombres = "$this->cliente_nombres $this->cliente_apellidos";
        $corte = $this->cliente_corte;
        $mensualidad = number_format($this->mensualidad, 2, '.', ',');
        $renta = number_format($this->costo_renta, 2, '.', ',');
        $instalacion = number_format($this->precio_instalacion, 2, '.', ',');
        $monto = number_format($this->mensualidad, 2, '.', ',');
        $message = preg_replace(['{{cliente}}', '{{corte}}', '{{monto}}', '{{mensualidad}}', '{{renta}}', '{{instalacion}}'], [$nombres, $corte, $monto, $mensualidad, $renta, $instalacion], $template[0]["template"]);
        $data = [
            "phone" => $brand[0]['codigo_pais'].$phone, 
            "message" => $message
        ];

        $this->whatsapp($data, $this->cliente_id, 'welcome');
    }


    /**
     * create               Metodo que crea un nuevo cliente
     * 
     * @return bool         Return true or false
     * 
     **/
    public function create($usuario_id)
    {
        // Existe el email?
        if ($this->repeat_email()) {
            $this->error_message = "El email: $this->cliente_email ya existe!";
            return false;
        }

        // Existe el equipo?
        if ($this->repeat_serie_modem_equipo()) {
            $this->error_message = "La serie del equipo: $this->serie_modem ya existe!";
            return false;
        }

        // Existe el telefono?
        if ($this->repeat_phone()) {
            $this->error_message = "El telefono $this->cliente_telefono ya existe!";
            return false;
        }

        // Existe la ipv4
        if ($this->repeat_address()) {
            $this->error_message = "La IPv4: $this->cliente_ip ya existe!";
            return false;
        }

        // Existe la mac?
        if ($this->repeat_mac()) {
            $this->error_message = "La MAC: $this->cliente_mac ya existe!";
            return false;
        }

        // Existe el numero de serie?
        if ($this->repeat_serie_modem()) {
            $this->error_message = "El numero de serie: $this->serie_modem ya existe!";
            return false;
        }

        // Guardar datos
        $this->insert_into_clientes();
        if ($this->error) return false;

        // Guardar en clientes servicios
        $this->insert_into_clientes_servicios();
        if ($this->error) return false;

        if ($this->servicio_adicional) {
            $this->insert_to_servicios_adicionales();
            if ($this->error) return false;
        }

        // Si el paquete no es gratis
        if ($this->cliente_paquete != 0) {
            // Asignar pagos hasta el actual periodo
            $this->generar_pagos_anteriores($usuario_id);
            // Enviar mensajes de bienvenida
            $this->send_welcome_whatsapp();
        }

        // Agregar al mikrotik
        $this->save_customer_in_mikrotik();

        // Guardar la ine del cliente
        $this->save_file_customer_ine($this->files);
        // Fin del proceso
        return true;
    }

    /**
     * delete_new_customer
     *
     * @return void
     */
    public function delete($cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            DELETE FROM clientes 
            WHERE cliente_id = ?
        ");
        $query->execute([ $cliente_id ]);
    }

    
    /**
     * generar_pagos_anteriores
     *
     * @param  mixed $usuario_id
     * @return void
     */
    public function generar_pagos_anteriores($usuario_id)
    {
        $periods = $this->get_all_periods();
        $current_period = $this->get_to_periods();
        $JUDGMENT = "INSERT INTO pagos VALUES ";
        // Generar la consulta
        for($i = 0; $i < count($periods); $i++) {
            $periodo_id = $periods[$i]["periodo_id"];
            if ($periods[$i]["periodo_id"] != $current_period[0]) {
                $JUDGMENT .= "(NULL, '$this->cliente_id', 0, 0, '$periodo_id', 'CLIENTE NUEVO', '$usuario_id', 'Pagos no recuperables para clientes nuevos', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL, NULL, 1, 1),";
            }
            if ($periods[$i]["periodo_id"] == $current_period[0]) break;
        }

        // Intentar insertar los pagos
        try {
            $SQL = substr($JUDGMENT, 0, -1);
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute();
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = "";
        }
    }

    
    /**
     * get_all_periods
     *
     * @return array
     */
    public function get_all_periods() 
    {
        $SQL = "SELECT CONCAT(periodo_id, YEAR(CURRENT_DATE)) as periodo_id FROM periodos";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * save_customer
     *
     * @return void
     */
    public function insert_into_clientes()
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO `clientes` 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $query->execute([
                $this->cliente_id, 
                $this->cliente_nombres, 
                $this->cliente_apellidos, 
                $this->cliente_email, 
                $this->cliente_telefono, 
                $this->cliente_telefono2, 
                $this->cliente_telegram, 
                $this->cliente_domicilio, 
                $this->cliente_ine, 
                $this->cliente_rfc, 
                $this->cliente_razon_social, 
                $this->cliente_ip, 
                $this->cliente_ap, 
                $this->cliente_mac, 
                $this->serie_modem, 
                $this->cliente_instalacion, 
                $this->mensualidad, 
                $this->costo_renta, 
                $this->precio_instalacion, 
                $this->comentario, 
                $this->cliente_maps_ubicacion, 
                $this->metodo_bloqueo, 
                $this->server,
                $this->interface_arp,
                $this->profiles,
                $this->name_secret,
                $this->password_secret
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message ="Error al agregar datos del cliente!";
        }
    }

    /**
     * insert_into_clientes_servicios
     *
     * @return void
     */
    public function insert_into_clientes_servicios()
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO `clientes_servicios`
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ");
            $query->execute([
                $this->cliente_id, 
                $this->cliente_tipo, 
                $this->cliente_paquete, 
                $this->cliente_status, 
                $this->cliente_corte, 
                $this->colonia, 
                $this->tipo_servicio, 
                $this->status_equipo, 
                $this->antena_instalada, 
                $this->modem_instalado, 
                $this->suspender
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Error en servicios del cliente!";
            $this->delete($this->cliente_id);
        }
    }


    public function insert_to_servicios_adicionales() {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO `servicios_adicionales` 
                VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP())
            ");
            $query->execute([
                $this->cliente_id, 
                $this->servicio_id, 
                $this->costo_servicio, 
                $this->equipo_instalado, 
                $this->serie_equipo, 
                $this->status_equipo_instalado,
                $this->cliente_paquete,
                $this->cliente_ip,
                $this->cliente_ap,
                $this->cliente_mac
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar servicios adicionales!";//$error->getMessage();
        }
    }

    /**
     *  update
     *  Actualizar la informacion de un cliente
     * 
     * @return bool
     * 
     **/
    public function update()
    {
        if ($this->repeat_email()) {
            $this->error_message = "El email: $this->cliente_email ya existe!";
            return false;
        }

        if ($this->repeat_phone()) {
            $this->error_message = "El telefono $this->cliente_telefono ya existe!";
            return false;
        }

        if ($this->repeat_address()) {
            $this->error_message = "La IPv4: $this->cliente_ip ya existe!";
            return false;
        }

        if ($this->repeat_mac()) {
            $this->error_message = "La MAC: $this->cliente_mac ya existe!";
            return false;
        }

        if ($this->repeat_serie_modem()) {
            $this->error_message = "El S/N: $this->serie_modem ya existe!";
            return false;
        }

        // Obtener los datos previos del cliente antes de cambiarlos
        $previus = $this->get_customer_mikrotik_data($this->cliente_id);
        $mikrotik_data = $this->mikrotik_data_by_cologne($this->colonia);
        $change_mikrotik = $previus[0]["mikrotik_id"] != $mikrotik_data[0]["mikrotik_id"];
        $change_method = $this->metodo_bloqueo != $previus[0]["metodo_bloqueo"];
        $change_ip = $previus[0]['cliente_ip'] != $this->cliente_ip;
        $change_mac = $previus[0]['cliente_mac'] != $this->cliente_mac;
            
        // Si cambia algun dato importanto es eliminable
        $is_deletable = $change_mikrotik || $change_method || $change_ip || $change_mac;

        if ($is_deletable) {
            $this->remove_customer_mikrotik($previus);
        }

        $this->update_in_customers();
        if ($this->error) return false;

        $this->update_in_customers_services();
        if ($this->error) return false;

        if (!$this->servicio_adicional) {
            $this->disabled_addicional_services();
            if ($this->error) return false;
        } 

        if ($this->servicio_adicional) {
            // Hay servicios adicionales para este cliente?
            $services = $this->get_addicional_services();
            if (empty($services)) {
                $this->insert_to_servicios_adicionales();
                if ($this->error) return false;
            } else {
                $this->update_addicional_services();
                if ($this->error) return false;
            }
        }
        
        $this->save_customer_in_mikrotik($previus);
        $this->save_file_customer_ine($this->files);

        return true;
    }

    /**
     * update_in_customers
     *
     * @return void
     */
    public function update_in_customers()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE `clientes` 
                SET `cliente_nombres`= ?,
                `cliente_apellidos`= ?,
                `cliente_email`= ?,
                `cliente_telefono`= ?,
                `cliente_telefono2`= ?,
                `cliente_telegram`= ?,
                `cliente_domicilio`= ?,
                `cliente_ine`= ?, 
                `cliente_rfc`= ?,
                `cliente_razon_social`= ?,
                `cliente_ip`= ?,
                `cliente_ap`= ?,
                `cliente_mac`= ?,
                `serie_modem`= ?,
                `mensualidad`= ?, 
                `costo_renta`= ?, 
                `precio_instalacion`= ?,
                `cliente_instalacion`= ?,
                `comentario`= ?,
                `cliente_maps_ubicacion`= ?,
                `metodo_bloqueo`= ?, 
                `server`= ?, 
                `interface_arp`= ?, 
                `profile`= ?,
                `user_pppoe` = ?,
                `password_pppoe` = ?
                WHERE cliente_id =  ?
            ");
            $query->execute([
                $this->cliente_nombres,
                $this->cliente_apellidos,
                $this->cliente_email,
                $this->cliente_telefono,
                $this->cliente_telefono2,
                $this->cliente_telegram,
                $this->cliente_domicilio,
                $this->cliente_ine,
                $this->cliente_rfc,
                $this->cliente_razon_social,
                $this->cliente_ip,
                $this->cliente_ap,
                $this->cliente_mac,
                $this->serie_modem,
                $this->mensualidad,
                $this->costo_renta,
                $this->precio_instalacion,
                $this->cliente_instalacion,
                $this->comentario,
                $this->cliente_maps_ubicacion,
                $this->metodo_bloqueo,
                $this->server,
                $this->interface_arp,
                $this->profiles,
                $this->name_secret,
                $this->password_secret,
                $this->cliente_id
            ]);
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar el cliente!";
        }
    }

    public function disabled_addicional_services() 
    {
        try {
            $SQL = "
                UPDATE servicios_adicionales 
                SET status_servicio = 'pausado' 
                WHERE cliente_id = ? 
                AND servicio_id = ?
            ";
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute([
                $this->cliente_id,
                $this->servicio_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar servicios adicionales";
        }
    }


    public function update_addicional_services()
    {
        try {
            $SQL = "
                UPDATE servicios_adicionales 
                SET servicio_id = ?,
                costo_servicio = ?,
                equipo_id = ?,
                serie_equipo = ?,
                equipo_status = ?,
                paquete = ?,
                wan = ?,
                ap = ?,
                mac = ?,
                status_servicio = 'activo',
                fecha_modificacion = CURRENT_TIMESTAMP()
                WHERE cliente_id = ?
            ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([
            $this->servicio_id,
            $this->costo_servicio,
            $this->equipo_instalado,
            $this->serie_equipo,
            $this->status_equipo_instalado,
            $this->cliente_paquete,
            $this->cliente_ip,
            $this->cliente_ap,
            $this->cliente_mac,
            $this->cliente_id
        ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar servicios adicionales";
        }
    }

    /**
     * update_in_customers_services
     *
     * @return void
     */
    public function update_in_customers_services()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE `clientes_servicios` SET
                `cliente_tipo`=?,
                `cliente_paquete`=?,
                `cliente_corte`=?,
                `colonia`=?,
                `tipo_servicio`=?,
                `status_equipo`=?,
                `antena_instalada`=?,
                `modem_instalado`=?,
                `suspender`=? 
                WHERE cliente_id = ?
            ");
            $query->execute([
                $this->cliente_tipo,
                $this->cliente_paquete,
                $this->cliente_corte,
                $this->colonia,
                $this->tipo_servicio,
                $this->status_equipo,
                $this->antena_instalada,
                $this->modem_instalado,
                $this->suspender,
                $this->cliente_id
            ]);
        } catch(Exception $error) {
            $this->error_message = "Error al actualizar datos del servicio!";
            $this->error = true;
        }
    }


    /**
     * update_image
     *
     * @param  mixed $files
     * @param  mixed $request
     * @return void
     */
    function update_image()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE clientes SET 
                cliente_ine = ? 
                WHERE cliente_id = ?
            ");
            $query->execute([
                $this->cliente_ine,
                $this->cliente_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar ine";
        }
    }

    /**
     * customer_enable
     *
     * @param  mixed $cliente_id          {ID} del cliente
     * @return void
     */
    public function customer_enable() : void
    {
        $this->change_status_customer(1);
        if ($this->error) return;

        $customer = $this->get_customer_mikrotik_data($this->cliente_id);
        $this->save_customer_in_mikrotik($customer);
    }


    /**
     * unsubscribe                    Dar de baja a un cliente
     * 
     * @param  mixed $cliente_id      {ID} del cliente al que se dara de baja
     * 
     * @return void                   Retorna el status del proceso
     * 
     */
    public function unsubscribe($cliente_id, $is_root)
    {
        if (!isset($cliente_id) || is_null($cliente_id)) {
            $this->error = true;
            $this->error_message = "No se selecciono ningun cliente!";
            return;
        }

        $this->cliente_id = $cliente_id;
        $customer = $this->get_customer_mikrotik_data($this->cliente_id);
        
        if ($customer[0][ "cliente_status" ] != 2 && !$is_root) {
            $this->error = true;
            $this->error_message = "El cliente no puede bloquearse si esta activo!";
            return;
        }

        $period = $this->get_to_periods();
        $payments = $this->get_payment_periods($period);

        if (!empty($payments)) {
            $this->error = true;
            $this->error_message = "El cliente tiene pagos vigentes registrados!";
            return;
        }

        $this->change_status_customer(3);
        if ($this->error) return;

        $this->end_negociations($cliente_id);
        $this->end_failures_reports($cliente_id);
        $this->disabled_services($cliente_id);
        $this->remove_customer_mikrotik($customer);
        $this->error = false;
    }


    /**
     * clear_in_customers
     *
     * Limpiar los datos del cliente
     * 
     * @param  string $cliente_id
     * @return boolean
     * 
     */
    public function clear_in_customers($cliente_id)
    {
        $SQL = "UPDATE clientes SET cliente_ip = NULL, cliente_mac = NULL, serie_modem = NULL, serie_equipo = NULL, cliente_email = NULL, cliente_telefono = NULL, cliente_instalacion = DATE(CURRENT_DATE()) WHERE cliente_id = '$cliente_id'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $error = intval($query->errorCode());
        if ($error != 0) {
            $this->error_message = "Error al limpiar cliente!";
            return false;
        }
        return true;
    }


    /**
     * change_status_customer
     *
     * Cambiar el status del cliente
     * 
     * @param  int $cliente_status
     * @return void
     * 
     */
    public function change_status_customer($cliente_status) : void
    {
        try {
            $ultima_suspension = $cliente_status == 2 
                ? 'CURRENT_DATE()' 
                : 'ultima_suspension';

            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios
                SET cliente_status = ?,
                ultima_suspension = ?
                WHERE cliente_id = ?
            ");
            $query->execute([ 
                $cliente_status, 
                $ultima_suspension,
                $this->cliente_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al habilitar el cliente!";
        }
    }

    /**
     * suspended_lots
     *
     * @param  string $request
     * @return void
     */
    public function suspended_lots($request)
    {
        $order = array('"', '[', ']');
        $replace = array("'", "(", ")");
        $ids = str_replace($order, $replace, $request);
        
        $this->process_lots_suspended($ids);

        if ($this->error) return;
        
        $ids = json_decode($request);
        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $cliente_id) {
                $this->disabled_services($cliente_id);
            }
        }
    }

    /**
     * process_lots_suspended
     *
     * @param  mixed $request
     * @return void
     */
    public function process_lots_suspended($ids)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios 
                SET cliente_status = 2 
                WHERE cliente_id IN $ids
            ");
            $query->execute();
        } catch(Exception $error) {
            $this->error = true;
            $this->error_message = "Ocurrio un error!";
        }
    }

    /**
     * select_customers_lots
     *
     * @param  mixed $request
     * @return array
     */
    public function select_customers_lots($request)
    {
        $order = array('"', '[', ']');
        $replace = array("'", "(", ")");
        $ids = str_replace($order, $replace, $request);
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) as nombres, 
                clientes.*, 
                clientes_servicios.*, 
                colonias.nombre_colonia, 
                colonias.mikrotik_control, 
                paquetes.nombre_paquete, 
                paquetes.ancho_banda, 
                status_equipo.nombre_status_equipo, 
                cortes_servicio.dia_pago, 
                clientes_status.status_id, 
                clientes_status.nombre_status, 
                mikrotiks.mikrotik_nombre, 
                antenas.modelo, 
                modem.modelo as modem, 
                servicios.nombre_servicio 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN servicios 
            ON clientes_servicios.tipo_servicio = servicios.servicio_id 
            INNER JOIN colonias 
            ON colonias.colonia_id = clientes_servicios.colonia 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            INNER JOIN clientes_status 
            ON clientes_servicios.cliente_status = clientes_status.status_id 
            INNER JOIN paquetes 
            ON paquetes.idpaquete = clientes_servicios.cliente_paquete 
            CROSS JOIN status_equipo 
            ON clientes_servicios.status_equipo = status_equipo.status_id 
            INNER JOIN cortes_servicio 
            ON cortes_servicio.corte_id = clientes_servicios.cliente_corte 
            CROSS JOIN antenas 
            ON clientes_servicios.antena_instalada = antenas.idantena 
            CROSS JOIN modem 
            ON clientes_servicios.modem_instalado = modem.idmodem 
            WHERE  clientes.cliente_id IN $ids
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * add_ap_ping -> Agregar una nueva ap
     *
     * @param  mixed $request
     * @return mixed
     */
    public static function add_ap_ping($request)
    {
        $SQL = "INSERT INTO addresses VALUES(NULL, '$request->name', '$request->address', '$request->type', NULL ,NULL, NULL, 1)";
        $query = Flight::gnconn()->prepare($SQL);
        return $query->execute();
    }


    
    /**
     * get_packages
     *
     * @param  mixed $cliente_paquete
     * @return array
     */
    public function get_packages($cliente_paquete) {
        $SQL = "SELECT * FROM paquetes WHERE idpaquete = $cliente_paquete";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }



    /**
     * save_customer_in_mikrotik          Agrega los datos del cliente al mikrotik
     * 
     * @return void
     **/
    public function save_customer_in_mikrotik($previus = [])
    {
        $status = isset($previus[0]['cliente_status']) ? $previus[0]['cliente_status'] : 6;

        $rows = $this->get_customer_mikrotik_data($this->cliente_id);

        $this->cliente_nombres = $rows[0]['cliente_nombres'];
        $this->cliente_apellidos = $rows[0]['cliente_apellidos'];
        $this->cliente_ip = $rows[0]['cliente_ip'];
        $this->cliente_mac = $rows[0]['cliente_mac'];
        $this->server = $rows[0]['server'];
        $this->interface_arp = $rows[0]['interface_arp'];
        $this->profiles = $rows[0]['profile'];
        $this->name_secret = $rows[0]['user_pppoe'];
        $this->password_secret = $rows[0]['password_pppoe'];

        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $port = $rows[0]["mikrotik_puerto"];
        
        $conn = new Mikrotik($ip, $user, $password, $port);
        $cliente_nombres = "$this->cliente_nombres $this->cliente_apellidos";

        if (!$conn->connected) {
            // Reintentar conexion
            $this->customer_fail_mikrotik(
                $rows[0]['cliente_id'], 
                $rows[0]['mikrotik_id'], 
                $rows[0]['server'], 
                $rows[0]['interface_arp'], 
                $rows[0]['profile'], 
                "all"
            );

            $this->firewall = false;
            $this->queues = false;
            $this->leases = false;
            $this->arp = false;
            $this->pppoe = false;
        }

        if ($conn->connected) {
            switch ($this->metodo_bloqueo) {
                case 'DHCP':
                    $conn->remove_from_filter_rules($this->cliente_mac);

                    $this->leases = $conn->add_from_dhcp_leases(
                        $cliente_nombres,
                        $this->cliente_ip,
                        $this->cliente_mac,
                        $this->server
                    );

                    $this->firewall = $conn->add_from_address_list(
                        $cliente_nombres, 
                        $this->cliente_ip,
                        "ACTIVE", 
                    );

                    $this->queues = $conn->add_from_queue_simple(
                        $cliente_nombres,
                        $this->cliente_ip,
                        $rows[0]["ancho_banda"],
                        $rows[0]["parent_queue"]
                    );

                    if ($status == 2) {
                        $conn->add_from_address_list(
                            $cliente_nombres, 
                            $this->cliente_ip,
                            "MOROSOS"
                        );
                    } 
                    
                    if ($status != 2) {
                        $conn->remove_from_address_list(
                            $this->cliente_ip, 
                            "MOROSOS"
                        );
                    }
                break;

                case 'ARP':
                    $conn->remove_from_filter_rules($this->cliente_mac);

                    $this->arp = $conn->add_from_arp_list(
                        $this->cliente_ip,
                        $this->cliente_mac,
                        $cliente_nombres,
                        $this->interface_arp
                    );

                    $this->firewall = $conn->add_from_address_list(
                        $cliente_nombres, 
                        $this->cliente_ip, 
                        "ACTIVE"
                    );

                    $this->queues = $conn->add_from_queue_simple(
                        $cliente_nombres,
                        $this->cliente_ip,
                        $rows[0]["ancho_banda"],
                        $rows[0]["parent_queue"]
                    );

                    if ($status == 2) {
                        $conn->add_from_address_list(
                            $cliente_nombres,
                            $this->cliente_ip,
                            "MOROSOS"
                        );
                    }

                    if ($status != 2) {
                        $conn->remove_from_address_list(
                            $this->cliente_ip, 
                            "MOROSOS"
                        );
                    }
                break;

                case 'PPPOE':
                    if (!is_null($this->cliente_mac)) {
                        $conn->remove_from_address_list(
                            $this->cliente_ip,
                            "MOROSOS"
                        );
    
                        $conn->remove_from_address_list(
                            $this->cliente_ip,
                            "ACTIVE"
                        );
    
                        $conn->remove_customer_queue(
                            $rows[0]["cliente_ip"]
                        );
    
                        $conn->remove_from_arp_list(
                            $rows[0]["cliente_ip"],
                            $rows[0]["cliente_mac"]
                        );
    
                        $conn->remove_from_dhcp_leases(
                            $rows[0]["cliente_ip"],
                            $rows[0]["cliente_mac"]
                        );
                    }

                    $disabled = $status == 2 ? "true" : "false";

                    $this->pppoe = $conn->add_from_secrets(
                        $this->name_secret, 
                        $this->password_secret,
                        $this->profiles,
                        $this->cliente_ip,
                        $disabled
                    );
                break;
                default: // Mostrar nada
            }
        }
    }


    /**
     * update_customer_in_mikrotik
     *
     * @return void
     */
    public function update_customer_in_mikrotik($previus)
    {
        $ip = $previus[0]["mikrotik_ip"];
        $user = $previus[0]["mikrotik_usuario"];
        $password = $previus[0]["mikrotik_password"];
        $port = $previus[0]["mikrotik_puerto"];
        $conn = new Mikrotik($ip, $user, $password, $port);
        if (!$conn->connected) {
            $this->customer_fail_update_mikrotik($previus);
            $this->firewall = false;
            $this->queues = false;
            $this->leases = false;
            $this->arp = false;
            $this->pppoe = false;
        }

        if ($conn->connected) {
            $paquete = $this->get_packages($this->cliente_paquete);
            $cliente_nombres = "$this->cliente_nombres $this->cliente_apellidos";
            switch ($previus[0]["metodo_bloqueo"]) {
                case 'DHCP':

                    $this->firewall = $conn->update_from_address_list(
                        $cliente_nombres, 
                        $previus[0]["cliente_ip"], 
                        $this->cliente_ip
                    );

                    if ($previus[0]['cliente_status'] == 2) {
                        $this->firewall = $conn->update_from_address_list(
                            $cliente_nombres, 
                            $previus[0]["cliente_ip"], 
                            $this->cliente_ip,
                            "MOROSOS"
                        );
                    }

                    $this->queues = $conn->update_from_queues(
                        $cliente_nombres, 
                        $previus[0]["cliente_ip"], 
                        $this->cliente_ip, 
                        $paquete[0]["ancho_banda"], 
                        $previus[0]["parent_queue"]
                    );

                    $this->leases = $conn->update_from_dhcp_leases(
                        $cliente_nombres, 
                        $previus[0]["cliente_ip"], 
                        $previus[0]["cliente_mac"], 
                        $this->cliente_ip, 
                        $this->cliente_mac,
                        $this->server
                    );
                    
                    if (
                        $previus[0]["cliente_status"] == 2 || 
                        $previus[0]["cliente_status"] == 3
                    ) {
                        $conn->add_from_address_list(
                            $cliente_nombres,
                            $this->cliente_ip,
                            "MOROSOS"
                        );
                    }

                    $conn->disconnect();
                break;

                case 'ARP':
                    $this->firewall = $conn->update_from_address_list(
                        $cliente_nombres, 
                        $previus[0]["cliente_ip"], 
                        $this->cliente_ip
                    );

                    $this->queues = $conn->update_from_queues(
                        $cliente_nombres, 
                        $previus[0]["cliente_ip"], 
                        $this->cliente_ip, 
                        $paquete[0]["ancho_banda"], 
                        $previus[0]["parent_queue"]
                    );

                    $this->arp = $conn->update_from_arp_list(
                        $cliente_nombres, 
                        $previus[0]["cliente_ip"], 
                        $previus[0]["cliente_mac"], 
                        $this->cliente_ip, 
                        $this->cliente_mac, 
                        $this->interface_arp
                    );

                    if (
                        $previus[0]["cliente_status"] == 2 || 
                        $previus[0]["cliente_status"] == 3
                    ) {
                        $conn->add_from_address_list(
                            $cliente_nombres,
                            $this->cliente_ip,
                            "MOROSOS"
                        );
                    }
                    $conn->disconnect();
                break;

                case 'PPPOE':
                    $disabled = $previus[0]["cliente_status"] == 2 ? "true" : "false";
                    
                    $this->pppoe = $conn->update_from_secret(
                        $previus[0]["profile"], 
                        $this->profiles,
                        $previus[0]["user_pppoe"],
                        $this->name_secret,
                        $this->password_secret,
                        $this->cliente_ip,
                        $disabled
                    );

                    $conn->disconnect();
                break;
                default: // Hacer nada
            }
        }
    }



    /**
     * 
     * remove_customer_mikrotik       Eliminar del mikrotik
     * 
     * @param  array $rows
     * @return void
     * 
     **/
    public function remove_customer_mikrotik($rows, $locket = false)
    {
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $port = $rows[0]["mikrotik_puerto"];
        $conn = new Mikrotik($ip, $user, $password, $port);
        if (isset($rows[0]["metodo_bloqueo"])) {
            switch ($rows[0]["metodo_bloqueo"]) {
                case 'DHCP':
                    $conn->remove_customer_queue(
                        $rows[0]['cliente_ip']
                    );

                    $conn->remove_from_address_list(
                        $rows[0]['cliente_ip'], 
                        "ACTIVE"
                    );

                    $conn->remove_from_address_list(
                        $rows[0]["cliente_ip"], 
                        "MOROSOS"
                    );

                    $conn->remove_from_dhcp_leases(
                        $rows[0]['cliente_ip'],
                        $rows[0]['cliente_mac']
                    );

                    $conn->disconnect();
                break;

                case 'ARP':
                    $conn->remove_customer_queue(
                        $rows[0]['cliente_ip']
                    );

                    $conn->remove_from_address_list(
                        $rows[0]['cliente_ip'], 
                        "ACTIVE"
                    );

                    $conn->remove_from_address_list(
                        $rows[0]['cliente_ip'], 
                        "MOROSOS"
                    );

                    $conn->remove_from_arp_list(
                        $rows[0]['cliente_ip'],
                        $rows[0]['cliente_mac']
                    );

                    $conn->disconnect();
                break;

                case 'PPPOE':
                    $conn->remove_customer_secrets(
                        $rows[0]["user_pppoe"], 
                        $rows[0]['profile']
                    );

                    $conn->remove_customer_actives(
                        $rows[0]["user_pppoe"], 
                        $rows[0]['profile']
                    );
                    
                    $conn->disconnect();
                break;
                default: // Hacer nada
            }
        }
    }

    /**
     * SaveINE
     * Guarda la imagen del ine del cliente
     * @param  mixed $files
     * @return bool
     **/
    public function save_file_customer_ine($files)
    {
        // Si no existen datos de archivos
        if (is_null($this->cliente_ine)) return true;

        $path = './assets/clientesimg/ines/';
        $tmp_name = isset($files->cliente_ine['tmp_name']) 
            ? $files->cliente_ine['tmp_name'] 
            : null;

        return move_uploaded_file($tmp_name, $path.$this->cliente_ine);
    }


    /**
     * get_customer_by_id               Obtener los detaller de un cliente
     * 
     * @return array            Devuelta un array con lo datos del cliente
     */
    public function get_customer_by_id()
    {
        $SQL = "
            SELECT clientes.*, CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) as nombres, clientes_servicios.*, tipo_servicios.nombre_servicio, colonias.nombre_colonia, colonias.mikrotik_control, mikrotiks.mikrotik_nombre, paquetes.nombre_paquete, modem.modelo as modem, clientes_status.status_id, clientes_status.nombre_status 
            FROM clientes 
            INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN tipo_servicios ON clientes_servicios.tipo_servicio = tipo_servicios.servicio_id 
            INNER JOIN colonias ON colonias.colonia_id = clientes_servicios.colonia 
            INNER JOIN mikrotiks ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            INNER JOIN clientes_status ON clientes_servicios.cliente_status = clientes_status.status_id 
            INNER JOIN paquetes ON paquetes.idpaquete = clientes_servicios.cliente_paquete 
            CROSS JOIN status_equipo ON clientes_servicios.status_equipo = status_equipo.status_id 
            INNER JOIN cortes_servicio ON cortes_servicio.corte_id = clientes_servicios.cliente_corte 
            CROSS JOIN modem ON clientes_servicios.modem_instalado = modem.idmodem WHERE  clientes.cliente_id = ?
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    
    /**
     * get_addicional_services
     *
     * @return array
     */
    public function get_addicional_services() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM servicios_adicionales
            WHERE cliente_id = ?
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * mikrotik_data
     *
     * Obtener los datos del mikrotik del cliente 
     * 
     * @param  string $cliente_id
     * @return array
     */
    private function get_customer_mikrotik_data($cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT clientes.cliente_id, CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, 
                clientes.server, 
                clientes.interface_arp, 
                clientes.profile, 
                clientes.user_pppoe, 
                clientes.password_pppoe, 
                clientes.cliente_nombres, 
                clientes.cliente_apellidos, 
                clientes.metodo_bloqueo, 
                clientes.cliente_ip, 
                clientes.cliente_mac,
                clientes.cliente_email, 
                clientes_servicios.tipo_servicio,
                clientes_servicios.suspender, 
                clientes_servicios.cliente_paquete, 
                clientes_servicios.cliente_status, 
                mikrotiks.mikrotik_id, 
                mikrotiks.mikrotik_ip, 
                mikrotiks.mikrotik_usuario, 
                mikrotiks.mikrotik_password, 
                mikrotiks.mikrotik_puerto,
                mikrotiks.parent_queue, 
                colonias.colonia_id, 
                paquetes.ancho_banda
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN paquetes 
            ON clientes_servicios.cliente_paquete = paquetes.idpaquete 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            WHERE clientes.cliente_id = ?
        ");
        $query->execute([ $cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function mikrotik_data_by_cologne($colonia) {
        $SQL = "SELECT * FROM mikrotiks INNER JOIN colonias ON mikrotiks.mikrotik_id = colonias.mikrotik_control WHERE colonias.colonia_id = '$colonia'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * get_to_periods
     *
     * @return array
     */
    public function get_to_periods() {
        $SQL = "SELECT DATE(CURRENT_DATE) AS to_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[0]["to_date"]);
        $periodo_actual = $fecha[1] . $fecha[0];
        $periodos = array($periodo_actual);
        return $periodos;
    }

    /**
     * get_after_periods
     * Obtener los periodos posteriores
     * @return array
     */
    public function get_after_periods() {
        $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL +1 MONTH)) AS after_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[0]["after_date"]);
        $periodo_posterior = $fecha[1] . $fecha[0];
        $periodos = array($periodo_posterior);
        return $periodos;
    }

        
    /**
     * get_before_periods
     * Obtener el periodo anterior
     * @return array
     */
    public function get_before_periods() {
        $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL -1 MONTH)) AS before_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[0]["before_date"]);
        $periodo_anterior = $fecha[1] . $fecha[0];
        $periodos = array($periodo_anterior);
        return $periodos;
    }

    /**
     * transform_array_string_in_sql
     *
     * @param  mixed $periods
     * @return string
     */
    public function transform_array_string_in_sql($periods)
    {
        $text = json_encode($periods);
        $order = array('"', '[', ']');
        $replace = array("'", "(", ")");
        $in = str_replace($order, $replace, $text);
        return $in;
    }


    public function get_payment_periods($period)
    {
        $IN_PERIODS = $this->transform_array_string_in_sql($period);
        $SQL = "SELECT * FROM pagos WHERE periodo_id IN $IN_PERIODS AND status_pago != 0 AND cliente_id = '$this->cliente_id'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * layoff_customer                           Suspender y bloquear servicio del cliente
     *
     * @param  mixed $cliente_id        {ID} del cliente al que se suspenderá
     * @return mixed                     Return tru o false 
     */
    public function layoff_customer($cliente_id)
    {
        $this->cliente_id = $cliente_id;
        $customer = $this->get_customer_by_id();

        if (
            $customer[0]["suspender"] == 0 || 
            $customer[0]["cliente_paquete"] == 0
        ) {
            $this->error = true;
            $this->error_message = "Este cliente es gratis, empresarial o de negocios!";
            return;
        }

        $period = $this->get_to_periods();
        $payments = $this->get_payment_periods($period);

        if (!empty($payments)) {
            $this->error = true;
            $this->error_message = "El cliente tiene pagos vigentes registrados!";
            return;
        }

        $this->change_status_customer(2);
        if ($this->error) return;

        $this->end_negociations($cliente_id);
        $this->end_failures_reports($cliente_id);
        $this->disabled_services($cliente_id);

        $template = $this->get_templates('suspension');
        $brand = $this->get_brand();
        $nombres = $customer[0]["nombres"];
        $phone = $brand[0]["codigo_pais"] . $customer[0]["cliente_telefono"];
        $corte = $customer[0]["cliente_corte"];
        $message = preg_replace(['{{cliente}}', '{{corte}}'], [$nombres, $corte], $template[0]["template"]);
        $data = array("phone" => $phone, "message" => $message);
        $this->whatsapp($data, $customer[0]['cliente_id'], 'layoff');
        $this->error = false;
    }


    public function end_negociations($cliente_id)
    {
        $SQL = "UPDATE negociaciones SET status_negociacion = 3 WHERE cliente_id = '$cliente_id'";
        $query = Flight::gnconn()->prepare($SQL);
        return $query->execute();
    }


    public function end_failures_reports($cliente_id)
    {
        $SQL = "UPDATE reportes_fallas SET status = 2 WHERE cliente_id = '$cliente_id'";
        $query = Flight::gnconn()->prepare($SQL);
        return $query->execute();
    }


    public function disabled_services($cliente_id)
    {
        $rows = $this->get_customer_mikrotik_data($cliente_id);
        $metodo_bloqueo = $rows[0]["metodo_bloqueo"];
        $cliente_status = $rows[0]["cliente_status"];
        $cliente_id = $rows[0]['cliente_id'];
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $port = $rows[0]["mikrotik_puerto"];
        $cliente_nombres = $rows[0]["cliente_nombres"]." ".$rows[0]["cliente_apellidos"];
        $conn = new Mikrotik($ip, $user, $password, $port);
        if (!$conn->connected) {
            $this->customer_fail_mikrotik(
                $rows[0]['cliente_id'], 
                $rows[0]['mikrotik_id'], 
                $rows[0]['server'], 
                $rows[0]['interface_arp'], 
                $rows[0]['profile'],
                'morosos'
            );

            $cliente_status == 3 && $this->customer_fail_mikrotik(
                $rows[0]['cliente_id'], 
                $rows[0]['mikrotik_id'], 
                $rows[0]['server'], 
                $rows[0]['interface_arp'], 
                $rows[0]['profile'], 
                'filter_rules'
            );

            $this->morosos = false;
        }
        
        if ($conn->connected) {
            switch ($metodo_bloqueo) {
                case "DHCP":
                    $this->morosos = $conn->add_from_address_list(
                        $cliente_nombres,
                        $rows[0]['cliente_ip'],
                        "MOROSOS"
                    );

                    $cliente_status == 3 && $conn->add_from_filter_rules(
                        $cliente_nombres,
                        $rows[0]["cliente_mac"]
                    );
                    
                    $conn->disconnect();
                break;
    
                case "ARP":
                    $this->morosos = $conn->add_from_address_list(
                        $cliente_nombres,
                        $rows[0]['cliente_ip'],
                        "MOROSOS"
                    );

                    $cliente_status == 3 && 
                        $conn->add_from_filter_rules(
                            $cliente_nombres,
                            $rows[0]['cliente_mac']
                        );
                    $conn->disconnect();
                break;

                case "PPPOE":
                    $this->morosos = $conn->disabled_secret(
                        $rows[0]["user_pppoe"],
                        $rows[0]['profile']
                    );

                    $this->morosos = $conn->remove_customer_actives(
                        $rows[0]["user_pppoe"],
                        $rows[0]['profile']
                    );

                    $cliente_status == 3 && 
                        $conn->add_from_filter_rules(
                            $cliente_nombres,
                            $rows[0]['cliente_mac']
                        );
                    $conn->disconnect();
                break;
                default: // Do nothing
            }
        }
    }
}