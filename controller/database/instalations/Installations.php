<?php

class Instalations extends Messenger {
    public $instalacion_id;
    public $cliente_id;
    public $empleado_captura;
    public $nombre_completo;
    public $cliente_nombres;
    public $cliente_apellidos;
    public $cliente_telefono;
    public $direccion;
    public $ubicacion_cliente;
    public $promotor_id;
    public $promotor_add;
    public $colonia_id;
    public $tecnico_asignado;
    public $fecha_agenda;
    public $horario_id;
    public $empleado_agenda;
    public $empleado_finalizacion;
    public $cliente_email;
    public $ubicacion_spliter;
    public $puerto_spliter;
    public $potencia_establecida;
    public $metraje_cable;
    public $servicios_instalados;
    public $costo_instalacion;
    public $receptor_pago;
    public $tipo_pago;
    public $instalacion_pagada;
    public $pago_promotor;
    public $status;
    public $metodo_bloqueo;
    public $usuario_pppoe;
    public $password_pppoe;
    public $profile_pppoe;
    public $remote_address;
    public $comentarios;
    public $fecha_recepcion;
    public $fecha_realizacion;
    public $fecha_finalizacion;
    public $error;
    public $error_message;
    public $dhcp;
    public $arp;
    public $pppoe;

    public function __construct($request = [], $files = [])
    {
        $this->instalacion_id = isset($request->instalacion_id)
            ? $request->instalacion_id
            : null;

        $this->tecnico_asignado = isset($request->tecnico_asignado) &&
            !empty($request->tecnico_asignado)
                ? $request->tecnico_asignado
                : null;
    
        $this->promotor_id = isset($request->promotor) &&
            !empty($request->promotor) 
                ? $request->promotor
                : null;

        $this->empleado_captura = isset($request->empleado_captura) &&
            !empty($request->empleado_captura)
                ? $request->empleado_captura
                : null;

        $this->empleado_agenda = isset($request->empleado_agenda) &&
            !empty($request->empleado_agenda)
                ? $request->empleado_agenda
                : null;

        $this->empleado_finalizacion = isset($request->empleado_finalizacion) &&
            !empty($request->empleado_finalizacion) 
                ? $request->empleado_finalizacion
                : null;

        $this->cliente_nombres = isset($request->cliente_nombres) &&
            !empty($request->cliente_nombres)
            ? $request->cliente_nombres
            : null;

        $this->cliente_apellidos = isset($request->cliente_apellidos) &&
            !empty($request->cliente_apellidos)
            ? $request->cliente_apellidos
            : null;

        $this->nombre_completo = $this->cliente_nombres.' '.$this->cliente_apellidos;
        
        $this->cliente_telefono = isset($request->cliente_telefono) &&
            !empty($request->cliente_telefono)
            ? $request->cliente_telefono
            : null;

        $this->cliente_email = isset($request->cliente_email) && 
            !empty($request->cliente_email)
                ? $request->cliente_email
                : null;

        $this->colonia_id = isset($request->colonia) &&
            !empty($request->colonia)
            ? $request->colonia
            : null;

        $this->direccion = isset($request->domicilio) &&
            !empty($request->domicilio)
            ? $request->domicilio
            : null;

        $this->ubicacion_cliente = isset($request->ubicacion) &&
            !empty($request->ubicacion)
            ? $request->ubicacion
            : null;

        $this->ubicacion_spliter = isset($request->ubicacion_spliter) && 
            !empty($request->ubicacion_spliter)
                ? $request->ubicacion_spliter
                : null;

        $this->puerto_spliter = isset($request->puerto_spliter) && 
            !empty($request->puerto_spliter)
                ? $request->puerto_spliter
                : null;

        $this->potencia_establecida = isset($request->potencia_establecida) && 
            !empty($request->potencia_establecida)
                ? $request->potencia_establecida
                : null;

        $this->metraje_cable = isset($request->metraje_cable) && 
            !empty($request->metraje_cable)
                ? $request->metraje_cable
                : null;

        $this->servicios_instalados = isset($request->servicios_instalados) && 
            !empty($request->servicios_instalados)
                ? $request->servicios_instalados
                : null;

        $this->costo_instalacion = isset($request->costo_instalacion) && 
            !empty($request->costo_instalacion)
                ? $request->costo_instalacion
                : 0;

        $this->receptor_pago = isset($request->receptor_pago) && 
            !empty($request->receptor_pago)
                ? $request->receptor_pago
                : null;

        $this->tipo_pago = isset($request->tipo_pago) && 
            !empty($request->tipo_pago)
                ? $request->tipo_pago
                : 'efectivo';

        $this->instalacion_pagada = isset($request->instalacion_pagada) && 
            !empty($request->instalacion_pagada)
                ? $request->instalacion_pagada
                : 0;

        $this->pago_promotor = isset($request->pago_promotor) && 
            !empty($request->pago_promotor)
                ? $request->pago_promotor
                : 0;

        // 1: Agendada, 2: Finalizada
        $this->status = isset($request->status) && 
            !empty($request->status)
                ? $request->status
                : 1; 

        $this->metodo_bloqueo = isset($request->metodo_bloqueo) && 
            !empty($request->metodo_bloqueo)
                ? $request->metodo_bloqueo
                : 'DHCP'; 

        $this->usuario_pppoe = isset($request->usuario_pppoe) && 
            !empty($request->usuario_pppoe)
                ? $request->usuario_pppoe
                : null;
        
        $this->password_pppoe = isset($request->password_pppoe) && 
            !empty($request->password_pppoe)
                ? $request->password_pppoe
                : null;

        $this->profile_pppoe = isset($request->profile_pppoe) && 
            !empty($request->profile_pppoe)
                ? $request->profile_pppoe
                : 'default';

        $this->remote_address = isset($request->remote_address) &&
            !empty($request->remote_address)
                ? $request->remote_address
                : "";

        $this->comentarios = isset($request->comentarios) && 
            !empty($request->comentarios)
                ? $request->comentarios
                : '';

        $this->fecha_recepcion = date('Y-m-d:h:m:s');

        $this->fecha_agenda = isset($request->fecha_agenda) &&
            !empty($request->fecha_agenda)
            ? $request->fecha_agenda
            :  date('Y-m-d', strtotime("$this->fecha_recepcion + 1 day"));

        $this->horario_id = isset($request->horario_id) 
            ? $request->horario_id
            : null;

        $this->fecha_realizacion = isset($request->fecha_realizacion) && 
            !empty($request->fecha_realizacion)
                ? $request->fecha_realizacion
                :  date('Y-m-d h:m:s');

        $this->fecha_finalizacion = isset($request->fecha_finalizacion) && 
            !empty($request->fecha_finalizacion)
                ? $request->fecha_finalizacion
                : date('Y-m-d h:m:s');

        $this->cliente_id = isset($request->cliente_id) 
            ? $request->cliente_id 
            : $this->generate_customer_id();
    }


    public function search_customer_by_name() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM clientes
            WHERE LOWER(cliente_nombres) = ?
            AND LOWER(cliente_apellidos) = ?
            AND cliente_id != ?
        ");
        $query->execute([ 
            $this->cliente_nombres, 
            $this->cliente_apellidos,
            $this->cliente_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }
    
    /**
     * search_installation_by_phone
     *
     * @return array
     */
    public function search_installation_by_phone() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM instalaciones
            WHERE instalaciones.cliente_telefono = ?
            AND instalaciones.instalacion_id != ?
        ");
        $query->execute([
            $this->cliente_telefono, 
            $this->instalacion_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function search_installation_by_email() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM instalaciones
            WHERE instalaciones.cliente_email = ?
            AND instalaciones.instalacion_id != ?
        ");
        $query->execute([
            $this->cliente_email, 
            $this->instalacion_id
        ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * search_customer_by_phone
     *
     * @return array
     */
    public function search_customer_by_phone() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM clientes
            INNER JOIN clientes_servicios
            ON clientes.cliente_id = clientes_servicios.cliente_id
            WHERE clientes.cliente_telefono = ?
            AND clientes_servicios.cliente_status 
            IN (1,2,4,5,6)
        ");
        $query->execute([ $this->cliente_telefono ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * search_customer_by_email
     *
     * @return array
     */
    public function search_customer_by_email() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM clientes
            INNER JOIN clientes_servicios
            ON clientes.cliente_id = clientes_servicios.cliente_id
            WHERE clientes.cliente_email = ?
            AND clientes_servicios.cliente_status 
            IN (1,2,4,5,6)
        ");
        $query->execute([ $this->cliente_email ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function insert_instalation()
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO `instalaciones` 
                VALUES (null,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);
            ");
            $query->execute([
                $this->cliente_id, $this->tecnico_asignado, $this->promotor_id, $this->empleado_captura, $this->empleado_agenda, $this->empleado_finalizacion, $this->cliente_nombres, $this->cliente_apellidos, $this->cliente_telefono, $this->cliente_email, $this->colonia_id, $this->direccion, $this->ubicacion_cliente, $this->ubicacion_spliter, $this->puerto_spliter, $this->potencia_establecida, $this->metraje_cable, $this->servicios_instalados, $this->costo_instalacion, $this->receptor_pago, $this->tipo_pago, $this->instalacion_pagada, $this->pago_promotor, $this->status, $this->metodo_bloqueo, $this->usuario_pppoe, $this->password_pppoe, $this->profile_pppoe, $this->remote_address, $this->comentarios, $this->fecha_recepcion, $this->fecha_agenda, $this->horario_id, $this->fecha_realizacion, $this->fecha_finalizacion
            ]);
            $this->instalacion_id = $this->last_insert_id();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Error al agregar la instalacion!";
        }
    }


    public function update_instalation() : void 
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE instalaciones
                SET tecnico_asignado = ?,
                promotor_id = ?,
                empleado_agenda = ?,
                empleado_finalizacion = ?,
                cliente_nombres = ?,
                cliente_apellidos = ?,
                cliente_telefono = ?,
                cliente_email = ?,
                colonia_id = ?,
                direccion = ?,
                ubicacion_cliente = ?,
                ubicacion_spliter = ?,
                puerto_spliter = ?,
                potencia_establecida = ?,
                metraje_cable = ?,
                servicios_instalados = ?,
                costo_instalacion = ?,
                receptor_pago = ?,
                tipo_pago = ?,
                instalacion_pagada = ?,
                pago_promotor = ?,
                status = ?,
                metodo_bloqueo = ?,
                usuario_pppoe = ?,
                password_pppoe = ?,
                profile_pppoe = ?,
                remote_address = ?,
                comentarios = ?,
                fecha_programada = ?,
                horario_id = ?,
                fecha_realizacion = ?,
                fecha_finalizacion = ?
                WHERE instalacion_id = ?
            ");
            $query->execute([
                $this->tecnico_asignado, $this->promotor_id, $this->empleado_agenda, $this->empleado_finalizacion, $this->cliente_nombres, $this->cliente_apellidos, $this->cliente_telefono, $this->cliente_email, $this->colonia_id, $this->direccion, $this->ubicacion_cliente, $this->ubicacion_spliter, $this->puerto_spliter, $this->potencia_establecida, $this->metraje_cable, $this->servicios_instalados, $this->costo_instalacion, $this->receptor_pago, $this->tipo_pago, $this->instalacion_pagada, $this->pago_promotor, $this->status, $this->metodo_bloqueo, $this->usuario_pppoe, $this->password_pppoe, $this->profile_pppoe, $this->remote_address, $this->comentarios, $this->fecha_agenda, $this->horario_id, $this->fecha_realizacion, $this->fecha_finalizacion, $this->instalacion_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar la instalacion!";
        }
    }

    
    /**
     * create_instalation
     *
     * @return void
     */
    public function create_instalation() : void
    {
        $search_date = $this->get_all_instalations([
            "fecha_agenda" => $this->fecha_agenda,
            "horario_id" => $this->horario_id
        ]);

        if (!empty($search_date)) {
            $this->error = true;
            $this->error_message = "El horario establecido no esta disponible, porfavor seleccione un horario diferente, o consulte las instalaciones pendientes!";
            return;
        }

        $customer_name = $this->search_customer_by_name();
        $phone_installation = $this->search_installation_by_phone();
        $phone_customer = $this->search_customer_by_phone();
        $email_installation = $this->search_installation_by_email();
        $email_customer = $this->search_customer_by_email();

        $is_registered = !empty($phone_installation) || 
            !empty($phone_customer) || 
            !empty($email_installation) || 
            !empty($email_customer) ||
            !empty($customer_name);

        if ($is_registered) {
            $this->error = true;
            $this->error_message = "Ya existe un registro en clientes o instalaciones para el nombre, telefono o email";
            return;
        }

        if (!is_null($this->colonia_id)) {
            $this->save_in_mikrotik();
        }

        $this->insert_instalation();

        if ($this->error) return;
        
        if (!$this->error) {
            $this->send_message_instalation();
            return;
        }
    }

    
    /**
     * update_instalation
     *
     * @return void
     */
    public function edit_instalation() : void 
    {
        $phone_installation = $this->search_installation_by_phone();
        $phone_customer = $this->search_customer_by_phone();
        $email_installation = $this->search_installation_by_email();
        $email_customer = $this->search_customer_by_email();
        
        $is_registered = !empty($phone_installation) || 
            !empty($phone_customer) || 
            !empty($email_installation) || 
            !empty($email_customer);

        if ($is_registered) {
            $this->error = true;
            $this->error_message = "Ya existe un registro en clientes o instalaciones para el nombre, telefono o email";
            return;
        }

        if (!is_null($this->colonia_id)) {
            $this->save_in_mikrotik();
        }

        $this->update_instalation();
    }

    
    /**
     * start_installation
     *
     * @return void
     */
    public function start_installation() : void
    {
        $this->change_status_installation();
        if ($this->error) return;
        $this->send_message_tecnico();
    }


    public function change_status_installation(): void
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE instalaciones
                SET status = ?
                WHERE instalacion_id = ?
            ");
            $query->execute([
                $this->status, 
                $this->instalacion_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Error al procesar la instalacion!";
        }
    }

    
    /**
     * get_total_installations
     *
     * @return array
     */
    public function get_total_installations(): array
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                status_instalaciones.nombre_status,
                status_instalaciones.status_id,
                COUNT(instalaciones.instalacion_id) AS total
            FROM status_instalaciones
            LEFT JOIN instalaciones 
            ON status_instalaciones.status_id = instalaciones.status
            GROUP BY status_instalaciones.status_id
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * get_all_instalations
     *
     * @param  mixed $filters
     * @return array
     */
    public function get_all_instalations($filters) : array
    {
        $search = isset($filters["search"]) ? $filters["search"] : null;
        $status = isset($filters["status"]) && strlen($filters["status"]) < 12 ? $filters["status"] : 'IN(1,2,3,4)';
        $cliente_id = isset($filters["cliente_id"]) ? $filters["cliente_id"] : null;
        $promotor_id = isset($filters["promotor_id"]) ? $filters["promotor_id"] : null;
        $tecnico_asignado = isset($filters["tecnico_asignado"]) ? $filters["tecnico_asignado"] : null;
        $fecha_agenda = isset($filters["fecha_agenda"]) ? $filters["fecha_agenda"] : null;
        $horario_id = isset($filters["horario_id"]) ? $filters["horario_id"] : null;
        $fecha_realizacion = isset($filters["fecha_realizacion"]) ? $filters["fecha_realizacion"] : null;
        $fecha_finalizacion = isset($filters["fecha_finalizacion"]) ? $filters["fecha_finalizacion"] : null;
        // $colonia_id = isset($filters["colonia_id"]) ? $filters["colonia_id"] : null;
        // $pagina = $filters["pagina"];
        // $empleado_captura = $filters["empleado_captura"];
        // $empleado_finalizacion = $filters["empleado_finalizacion"];
        // $empleado_agenda = $filters["empleado_agenda"];
        // $receptor_pago = $filters["receptor_pago"];
        // $fecha_recepcion = $filters["fecha_recepcion"];

        $SQL = " 
            SELECT
                CONCAT(tecnico.empleado_nombre, ' ', tecnico.empleado_apellido) AS nombre_tecnico,
                CONCAT(captura.empleado_nombre, ' ', captura.empleado_apellido) AS nombre_empleado_captura,
                CONCAT(promotores.empleado_nombre, ' ', promotores.empleado_apellido) AS nombre_promotor,
                tecnico.usuario_id AS usuario_tecncio,
                captura.usuario_id AS usuario_captura,
                clientes.cliente_telefono AS registro_telefono,
                clientes.cliente_email AS registro_email,
                instalaciones.*,
                status_instalaciones.*
            FROM instalaciones 
            LEFT JOIN clientes 
            ON instalaciones.cliente_telefono = clientes.cliente_telefono
            AND instalaciones.cliente_email = clientes.cliente_email
            INNER JOIN empleados as promotores 
            ON instalaciones.promotor_id = promotores.empleado_id
            INNER JOIN status_instalaciones
            ON instalaciones.status = status_instalaciones.status_id
            LEFT JOIN empleados AS tecnico
            ON instalaciones.tecnico_asignado = tecnico.empleado_id
            LEFT JOIN empleados AS captura
            ON instalaciones.empleado_captura = captura.empleado_id
            WHERE instalaciones.status $status
        ";        

        if (!is_null($cliente_id)) {
            $SQL .= " AND instalaciones.cliente_id = '$cliente_id' ";
        }

        if (!is_null($promotor_id)) {
            $SQL .= " AND instalaciones.promotor_id = '$promotor_id' ";
        }

        if (!is_null($tecnico_asignado)) {
            $SQL .= " AND instalaciones.tecnico_asignado = '$tecnico_asignado' ";
        }

        if (!is_null($fecha_agenda)) {
            $SQL .= " AND DATE(instalaciones.fecha_programada) = DATE('$fecha_agenda') ";
        }

        if (!is_null($horario_id)) {
            $SQL .= " AND instalaciones.horario_id = '$horario_id' ";
        }

        if (!is_null($fecha_realizacion)) {
            $SQL .= " AND DATE(instalaciones.fecha_realizacion) = '$fecha_realizacion' ";
        }

        if (!is_null($fecha_finalizacion)) {
            $SQL .= " AND DATE(instalaciones.fecha_finalizacion) = '$fecha_finalizacion' ";
        }

        if (!is_null($search)) {
            $SQL .= "
                AND CONCAT(instalaciones.cliente_nombres, ' ', instalaciones.cliente_apellidos)
                LIKE '%$search%' 
                OR instalaciones.cliente_telefono
                LIKE '%$search%'
                OR instalaciones.cliente_email
                LIKE '%$search%'
                OR CONCAT(tecnico.empleado_nombre, ' ', tecnico.empleado_apellido)
                LIKE '%$search%'
                OR CONCAT(captura.empleado_nombre, ' ', captura.empleado_apellido)
                LIKE '%$search%'
                OR  CONCAT(promotores.empleado_nombre, ' ', promotores.empleado_apellido)
                LIKE '%$search%'
            ";
        }

        $SQL .= " ORDER BY instalaciones.instalacion_id DESC";

        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * save_promotor
     *
     * @param  mixed $promotor
     * @return void
     */
    public function save_promotor($promotor) : void
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO `promotores`
                VALUES (null, ?, 1)
            ");
            $query->execute([ $promotor ]);
            $this->promotor_id = $this->last_insert_id();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar el promotor!";
        }
    }


    public function get_instalation_byid($instalacion_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT
                CONCAT(tecnico.empleado_nombre, ' ', tecnico.empleado_apellido) AS nombre_tecnico,
                CONCAT(captura.empleado_nombre, ' ', captura.empleado_apellido) AS nombre_empleado_captura,
                CONCAT(promotores.empleado_nombre, ' ', promotores.empleado_apellido) AS nombre_promotor,
                tecnico.usuario_id AS usuario_tecncio,
                tecnico.empleado_telefono AS tecnico_telefono,
                captura.usuario_id AS usuario_captura,
                clientes.cliente_telefono AS registro_telefono,
                clientes.cliente_email AS registro_email,
                instalaciones.*,
                status_instalaciones.*
            FROM instalaciones 
            LEFT JOIN clientes 
            ON instalaciones.cliente_telefono = clientes.cliente_telefono
            AND instalaciones.cliente_email = clientes.cliente_email
            INNER JOIN empleados as promotores 
            ON instalaciones.promotor_id = promotores.empleado_id
            INNER JOIN status_instalaciones
            ON instalaciones.status = status_instalaciones.status_id
            LEFT JOIN empleados AS tecnico
            ON instalaciones.tecnico_asignado = tecnico.empleado_id
            LEFT JOIN empleados AS captura
            ON instalaciones.empleado_captura = captura.empleado_id
            WHERE instalaciones.instalacion_id = ?
        ");
        $query->execute([ $instalacion_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * get_cologne_byid
     *
     * @param  mixed $colonia_id
     * @return array
     */
    public function get_cologne_byid($colonia_id): array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM colonias
            WHERE colonia_id = ?
        ");
        $query->execute([ $colonia_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }
    
    
    /**
     * last_insert_id
     *
     * @return mixed
     */
    public function last_insert_id()
    {
        return Flight::gnconn()->lastInsertID();
    }
    
    /**
     * send_message_instalation
     *
     * @return void
     */
    public function send_message_instalation()
    {
        $enterprice = $this->get_brand();
        $template = $this->get_templates('instalacion_cliente');
        $phone = $enterprice[0]['codigo_pais'].$this->cliente_telefono;
        $message = "*".$template[0]['title']."*\n\n".preg_replace(['{{cliente}}', '{{costo}}', '{{telefono}}', '{{fecha_agenda}}', '{{enterprice}}'], [$this->nombre_completo, number_format($this->costo_instalacion, 2), $this->cliente_telefono, $this->fecha_agenda, $enterprice[0]['name']], $template[0]['template']);
        $data = ["phone" => $phone, "message" => $message];
        $this->whatsapp($data, $this->cliente_id, 'instalacion_cliente');
    }

    
    /**
     * send_message_tecnico for instalation
     *
     * @return void
     */
    public function send_message_tecnico() : void
    {
        $enterprice = $this->get_brand();
        $installation = $this->get_instalation_byid($this->instalacion_id);
        $colonia = $this->get_cologne_byid($installation[0]['colonia_id']);
        $template = $this->get_templates('instalar_servicio');
        $phone = $enterprice[0]['codigo_pais'].$installation[0]['tecnico_telefono'];
        $tecnico = $installation[0]['nombre_tecnico'];
        $nombre_completo = $installation[0]['cliente_nombres'].' '.$installation[0]['cliente_apellidos'];
        $telefono = $installation[0]['cliente_telefono'];
        $direccion = $installation[0]['direccion'];
        $ubicacion = $installation[0]['ubicacion_cliente'];
        $colonia_name = $colonia[0]['nombre_colonia'];

        $message = "*".$template[0]['title']."*\n\n".preg_replace(
            ['{{tecnico}}', '{{cliente}}', '{{telefono}}', '{{direccion}}', '{{ubicacion}}', '{{colonia}}'], 
            [$tecnico, $nombre_completo, $telefono, $direccion, $ubicacion, $colonia_name], 
            $template[0]['template']
        );
        
        $data = ["phone" => $phone, "message" => $message];
        $this->whatsapp($data, $installation[0]['cliente_id'], 'instalar_servicio');
    }

    public function generate_customer_id()
    {
        $brand = $this->get_brand();
        // $colonia = $this->get_cologne_byid($this->colonia_id);
        $enterpricename = substr($brand[0]["name"], 0, 4);
        $SQL = "SELECT COUNT(cliente_id) as total FROM clientes";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $total = $rows[0]['total'];

        $name = substr($this->cliente_nombres, 0, 2);
        $lastname = substr($this->cliente_apellidos, 0, 2);
        $ID = strtoupper(trim($enterpricename) . trim($name) . trim($lastname) . $total);
        $cliente_id = str_replace(" ", "", $ID);
        return $cliente_id;
    }

    
    /**
     * get_mikrotik_by_cologne
     *
     * @param  mixed $colonia
     * @return array
     */
    public function get_mikrotik_by_cologne($colonia): array
    {
        $query = Flight::gnconn()->prepare("
            SELECT mikrotiks.* FROM colonias
            INNER JOIN mikrotiks
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id
            WHERE colonias.colonia_id = ?
        ");
        $query->execute([ $colonia ]);
        $rows = $query->fetchAll();
        return $rows;
    }
    
    /**
     * save_in_mikrotik
     *
     * @return void
     */
    public function save_in_mikrotik(): void 
    {
        $rows = $this->get_mikrotik_by_cologne($this->colonia_id);

        $conn = new Mikrotik(
            $rows[0]['mikrotik_ip'],
            $rows[0]['mikrotik_usuario'],
            $rows[0]['mikrotik_password'],
            $rows[0]['mikrotik_puerto']
        );

        if (!$conn->connected) {
            $this->dhcp = [
                "active" => false,
                "leases" => false,
                "queues" => false
            ];

            $this->arp = [
                "arp" => false,
                "queue" => false,
                "active" => false
            ];

            $this->pppoe = [
                "secret" => false
            ];
        }

        if ($conn->connected) {
            switch($this->metodo_bloqueo) {
                case 'DHCP': 
                    $this->dhcp = [
                        "active" => true,
                        "leases" => true,
                        "queues" => true
                    ];
    
                    $this->arp = [
                        "arp" => true,
                        "queue" => true,
                        "active" => true
                    ];
    
                    $this->pppoe = [
                        "secret" => true
                    ];
                break;
    
                case 'ARP':
                    $this->dhcp = [
                        "active" => true,
                        "leases" => true,
                        "queues" => true
                    ];
    
                    $this->arp = [
                        "arp" => true,
                        "queue" => true,
                        "active" => true
                    ];
    
                    $this->pppoe = [
                        "secret" => true
                    ];
                break;
    
                case 'PPPOE':
                    $this->dhcp = [
                        "active" => true,
                        "leases" => true,
                        "queues" => true
                    ];
    
                    $this->arp = [
                        "arp" => true,
                        "queue" => true,
                        "active" => true
                    ];
    
                    $this->pppoe = [
                        "secret" => $conn->add_from_secrets(
                            $this->usuario_pppoe, 
                            $this->password_pppoe, 
                            $this->profile_pppoe, 
                            $this->remote_address
                        )
                    ];
                break;
    
                default: // No hacer nada
            }
        }
    }
}