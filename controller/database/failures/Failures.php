<?php

class Failures extends Messenger
{

    public $reporte_id;
    public $cliente_id;
    public $usuario_captura;
    public $empleado_control;
    public $tecnico;
    public $usuario_finalizacion;
    public $reporte_domicilio;
    public $reporte_referencia;
    public $reporte_descripcion;
    public $prioridad;
    public $fail_list;
    public $cliente_activo;
    public $cliente_mikrotik;
    public $cliente_morosos;
    public $activo_mikrotik;
    public $atencion_online;
    public $fecha_captura;
    public $fecha_atencion;
    public $fecha_finalizacion;
    public $vehiculo;
    public $materiales;
    public $procesos;
    public $efectivo;
    public $solucionada;
    public $comentarios;
    public $status;
    public $is_active;
    public $client_active;
    public $error;
    public $error_message;

    public $firewall;
    public $queues;
    public $leases;
    public $arp;
    public $pppoe;

    public function __construct($request = [])
    {
        $this->reporte_id = isset($request->reporte_id) 
            ? $request->reporte_id 
            : null;

        $this->cliente_id = isset($request->cliente_id) 
            ? $request->cliente_id 
            : null;

        $this->usuario_captura = isset($request->usuario_captura) 
            ? $request->usuario_captura 
            : null;

        $this->empleado_control = isset($request->empleado_control) 
            ? $request->empleado_control 
            : null;

        $this->prioridad = isset($request->prioridad) 
            ? $request->prioridad 
            : 1;

        $this->tecnico = isset($request->tecnico) 
            ? $request->tecnico 
            : null;

        $this->reporte_domicilio = isset($request->reporte_domicilio) 
            ? $request->reporte_domicilio 
            : null;

        $this->fail_list = isset($request->fail_list) && 
            $request->fail_list != 0
                ? $request->fail_list
                : null;

        $this->cliente_mikrotik = isset($request->cliente_mikrotik) 
            ? $request->cliente_mikrotik
            : null;

        $this->status = isset($request->status) 
            ? $request->status
            : 1;

        $this->reporte_descripcion = is_null($this->fail_list) && 
            isset($request->reporte_descripcion) &&
            !empty($request->reporte_descripcion)
                ? $request->reporte_descripcion 
                : $this->fail_list;

        $this->fecha_captura = date('Y-m-d');

        $this->fecha_atencion = isset($request->fecha_atencion) 
            ? $request->fecha_atencion 
            : date('Y-m-d');

        $this->vehiculo = isset($request->vehiculo) 
            ? $request->vehiculo 
            : null;

        $this->materiales = isset($request->material) 
            ? $request->material 
            : null;

        $this->procesos = isset($request->procesos) 
            ? $request->procesos 
            : null;

        $this->efectivo = isset($request->efectivo) 
            ? $request->efectivo 
            : 0;

        $this->comentarios = isset($request->comentarios) 
            ? $request->comentarios 
            : null;

        $this->is_report_pending();
        $this->is_client_active();

        if (is_null($this->fail_list) && isset($this->reporte_descripcion) && !empty($this->reporte_descripcion)) {
            $this->save_new_fail_common();
        }
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
     * is_exists
     *
     * @return bool
     * Verificar que no tenga reportes activos
     * 
     **/
    public function is_report_pending()
    {
        $rows = $this->search_report_active();

        if (empty($rows)) {
            $this->is_active = false;
        }

        if (!empty($rows)) {
            $this->is_active = true;
        }

        return $this->is_active;
    }

    
    /**
     * search_report_active()
     *
     * @return array
     */
    public function search_report_active() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM reportes_fallas 
            WHERE cliente_id = ? 
            AND status IN (1,2)
            AND YEAR(fecha_captura) = YEAR(current_date)
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * is_client_active
     *
     * @return bool
     * Verificar que el cliente este activo
     * 
     **/
    public function is_client_active()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM clientes_servicios 
            WHERE cliente_id = ? 
            AND cliente_status != 2 
            AND cliente_status != 3
        ");
        $query->execute([ $this->cliente_id ]);
        $rows = $query->fetchAll();

        if (empty($rows)) {
            $this->client_active = false;
        }

        if (!empty($rows)) {
            $this->client_active = true;
        }

        return $this->client_active;
    }


    public function send_employee_message() 
    {
        $reporte = $this->get_failure_by_id();
        $template = $this->get_templates('atencion_reporte');
        $brand = $this->get_brand();

        $phone = $reporte[0]["empleado_telefono"];
        $direccion = $reporte[0]["reporte_domicilio"];
        $ubicacion = $reporte[0]["cliente_maps_ubicacion"];
        $colonia = $reporte[0]["nombre_colonia"];
        $empleado_id = $reporte[0]["tecnico"];
        $cliente = $reporte[0]["cliente"];
        $telefono = $reporte[0]["cliente_telefono"];
        $tecnico = $reporte[0]["empleado_tecnico"];
        $reporte = $reporte[0]["reporte_descripcion"];

        $message = "*".$template[0]['title']."*\n\n". preg_replace(['{{tecnico}}', '{{reporte}}', '{{cliente}}', '{{telefono}}', '{{direccion}}', '{{ubicacion}}','{{colonia}}'], [$tecnico, $reporte, $cliente, $telefono, $direccion, $ubicacion, $colonia], $template[0]["template"]);
        $customer_message = ["phone" => $brand[0]["codigo_pais"].$phone, "message" => $message];

        $this->whatsapp($customer_message, $empleado_id, 'atencion_reporte');
    }


    public function send_message_whatsapp()
    {
        $customer = $this->get_customer_by_id();
        $template = $this->get_templates('reporte_falla');
        $brand = $this->get_brand();

        $nombres = $customer[0]["nombres"];
        $phone = $customer[0]["cliente_telefono"];
        $cliente_id = $customer[0]["cliente_id"];

        $message = "*".$template[0]['title']."*\n\n". preg_replace(
            ['{{cliente}}'], 
            [$nombres],
            $template[0]["template"]
        );

        $customer_message = [
            "phone" => $brand[0]["codigo_pais"].$phone,
            "message" => $message
        ];

        $this->whatsapp($customer_message, $cliente_id, 'reporte_falla');
    }


    public function save_report()
    {
        try {
            $query = Flight::gnconn()->prepare("INSERT INTO reportes_fallas VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->execute([ $this->cliente_id, $this->usuario_captura, $this->empleado_control, $this->prioridad, $this->tecnico, $this->usuario_finalizacion, $this->reporte_domicilio, $this->reporte_descripcion, $this->fecha_captura, $this->fecha_atencion, $this->fecha_finalizacion, $this->vehiculo, $this->materiales, $this->procesos, $this->comentarios, $this->efectivo, $this->status ]);
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al agregar falla!";
        }
    }


    public function get_customer_by_id()
    {
        $SQL = "
            SELECT clientes.*, CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, clientes_servicios.*, tipo_servicios.nombre_servicio, colonias.nombre_colonia, colonias.mikrotik_control, mikrotiks.mikrotik_nombre, paquetes.nombre_paquete, modem.modelo AS modem, clientes_status.status_id, clientes_status.nombre_status 
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

    public function get_employee_by_id()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM empleados
            WHERE empleado_id = ?
        ");
        $query->execute([ $this->tecnico ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * Create
     *
     * @return void
     * Crear un nuevo reporte
     * 
     **/
    public function create()
    {
        if ($this->cliente_mikrotik) {
            $this->save_customer_in_mikrotik();
        }

        $this->save_report();
        $this->send_message_whatsapp();
    }

    
    /**
     * update
     *
     * @return void
     */
    public function update() : void
    {
        try {
            $query = Flight::gnconn()->prepare(" UPDATE `reportes_fallas` SET `empleado_control` = ?, `prioridad` = ?, `tecnico` = ?, `usuario_finalizacion` = ?, `reporte_domicilio` = ?, `reporte_descripcion` = ?, `fecha_atencion` = ?, `fecha_finalizacion` = ?, `vehiculo` = ?,`materiales` = ?, `procesos` = ?, `comentarios` = ?, `efectivo` = ?,`status` = ? WHERE `reporte_id` = ? ");
            $query->execute([ $this->empleado_control, $this->prioridad, $this->tecnico, $this->usuario_finalizacion, $this->reporte_domicilio, $this->reporte_descripcion, $this->fecha_atencion, $this->fecha_atencion, $this->vehiculo, $this->materiales, $this->procesos, $this->comentarios, $this->efectivo, $this->status, $this->reporte_id ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar el reporte!";
        }
    }


    public function change_status($reporte_id)
    {
        $this->reporte_id = $reporte_id;
        $this->update_status($this->reporte_id, 2);

        if ($this->error) return;
        $this->send_employee_message();
    }


    public function update_status($reporte_id, $status)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE reportes_fallas
                SET status = ?
                WHERE reporte_id = ?
            ");
            $query->execute([ 
                $status, 
                $reporte_id 
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al actualizar el status del reporte";//$error->getMessage();
        }
    }


    public function save_new_fail_common()
    {
        try {
            $query = Flight::gnconn()->prepare("
                INSERT INTO reportes_comunes
                VALUES (null, ?, ?)
            ");
            $query->execute([ 
                $this->reporte_descripcion, 
                $this->prioridad 
            ]);
        } catch (Exception $error) {
            $this->error_message = $error->getMessage();
        }
    }


    /**
     * get_fails_by_customer
     *
     * @param  mixed $cliente_id
     * @return array
     */
    public function get_failure_by_customer_id($cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM reportes_fallas 
            CROSS JOIN clientes 
            ON reportes_fallas.cliente_id = clientes.cliente_id
            CROSS JOIN usuarios
            ON reportes_fallas.empleado_control = usuarios.usuario_id
            LEFT JOIN empleados 
            ON usuarios.usuario_id = empleados.usuario_id
            WHERE reportes_fallas.cliente_id = ?
        ");
        $query->execute([ $cliente_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    
    /**
     * get_failures_filter
     *
     * @param  array $filters
     * @return array
     */
    public function get_failures($filters) : array
    {
        $status = $filters["status"];
        $prioridad = $filters["prioridad"];
        $search = $filters["search"];
        $fecha_inicio = $filters["fecha_inicio"];
        $fecha_fin = $filters["fecha_fin"];
        $cliente_id = $filters["cliente_id"];

        $sql = "
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS cliente,  
                CONCAT(captura.empleado_nombre, ' ', captura.empleado_apellido) AS empleado_captura,  
                CONCAT(control.empleado_nombre, ' ', control.empleado_apellido) AS empleado_cargo,  
                CONCAT(tecnico.empleado_nombre, ' ', tecnico.empleado_apellido) AS empleado_tecnico,  
                CONCAT(finalizacion.empleado_nombre, ' ', finalizacion.empleado_apellido) AS empleado_finalizacion,  
                prioridad_reportes.*,  
                reportes_fallas.*,  
                status_reportes.*, 
                clientes.*,
                colonias.*
            FROM reportes_fallas
            INNER JOIN clientes 
            ON reportes_fallas.cliente_id = clientes.cliente_id
            CROSS JOIN clientes_servicios
            ON reportes_fallas.cliente_id = clientes_servicios.cliente_id
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id
            INNER JOIN prioridad_reportes 
            ON reportes_fallas.prioridad = prioridad_reportes.id
            INNER JOIN status_reportes 
            ON reportes_fallas.status = status_reportes.id
            LEFT JOIN empleados AS captura 
            ON reportes_fallas.usuario_captura = captura.usuario_id
            LEFT JOIN empleados AS control 
            ON reportes_fallas.empleado_control = control.empleado_id
            LEFT JOIN empleados AS tecnico 
            ON reportes_fallas.tecnico = tecnico.empleado_id
            LEFT JOIN empleados AS finalizacion 
            ON reportes_fallas.usuario_finalizacion = finalizacion.usuario_id
            WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE)
        ";

        if (!is_null($status)) {
            $sql .= " AND reportes_fallas.status = $status ";
        }

        if (!is_null($prioridad)) {
            $sql .= " AND reportes_fallas.prioridad = $prioridad ";
        }

        if (!is_null($cliente_id)) {
            $sql .= " AND reportes_fallas.cliente_id = '$cliente_id' ";
        }

        if (!is_null($fecha_inicio) && !is_null($fecha_fin)) {
            $sql .= "
                AND DATE(reportes_fallas.fecha_captura) >= DATE($fecha_inicio) 
                AND DATE(reportes_fallas.fecha_captura) <= DATE($fecha_fin)
            ";
        }

        if (!is_null($search)) {
            $sql .= "
                AND reportes_fallas.tecnico LIKE '%$search%'
                OR CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) 
                LIKE '%$search%'
                OR CONCAT(tecnico.empleado_nombre, ' ', tecnico.empleado_apellido) 
                LIKE '%$search%'
            ";
        }

        $sql .= " ORDER BY reportes_fallas.status ASC ";

        $query = Flight::gnconn()->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_status_failures()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM status_reportes
            ORDER BY id ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }
    
    
    /**
     * get_failure_by_id
     *
     * @return array
     */
    public function get_failure_by_id() : array
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS cliente,
                CONCAT(captura.empleado_nombre, ' ', captura.empleado_apellido) AS empleado_captura,
                CONCAT(control.empleado_nombre, ' ', control.empleado_apellido) AS empleado_cargo,
                CONCAT(tecnico.empleado_nombre, ' ', tecnico.empleado_apellido) AS empleado_tecnico,
                CONCAT(finalizacion.empleado_nombre, ' ', finalizacion.empleado_apellido) AS empleado_finalizacion,
                prioridad_reportes.*,
                reportes_fallas.*,
                status_reportes.*,
                clientes.*,
                colonias.*,
                tecnico.*
            FROM reportes_fallas
            INNER JOIN clientes 
            ON reportes_fallas.cliente_id = clientes.cliente_id
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id
            INNER JOIN prioridad_reportes 
            ON reportes_fallas.prioridad = prioridad_reportes.id
            INNER JOIN status_reportes 
            ON reportes_fallas.status = status_reportes.id
            LEFT JOIN empleados AS captura 
            ON reportes_fallas.usuario_captura = captura.usuario_id
            LEFT JOIN empleados AS control 
            ON reportes_fallas.empleado_control = control.empleado_id
            LEFT JOIN empleados AS tecnico 
            ON reportes_fallas.tecnico = tecnico.empleado_id
            LEFT JOIN empleados AS finalizacion 
            ON reportes_fallas.usuario_finalizacion = finalizacion.usuario_id
            WHERE reportes_fallas.reporte_id = ?
        ");
        $query->execute([ $this->reporte_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_all_failures() 
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS cliente,
                CONCAT(captura.empleado_nombre, ' ', captura.empleado_apellido) AS empleado_captura,
                CONCAT(control.empleado_nombre, ' ', control.empleado_apellido) AS empleado_cargo,
                CONCAT(tecnico.empleado_nombre, ' ', tecnico.empleado_apellido) AS empleado_tecnico,
                CONCAT(finalizacion.empleado_nombre, ' ', finalizacion.empleado_apellido) AS empleado_finalizacion,
                prioridad_reportes.*,
                reportes_fallas.*,
                status_reportes.*,
                clientes.*,
                colonias.*,
                tecnico.*
            FROM reportes_fallas
            INNER JOIN clientes 
            ON reportes_fallas.cliente_id = clientes.cliente_id
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id
            INNER JOIN prioridad_reportes 
            ON reportes_fallas.prioridad = prioridad_reportes.id
            INNER JOIN status_reportes 
            ON reportes_fallas.status = status_reportes.id
            LEFT JOIN empleados AS captura 
            ON reportes_fallas.usuario_captura = captura.usuario_id
            LEFT JOIN empleados AS control 
            ON reportes_fallas.empleado_control = control.empleado_id
            LEFT JOIN empleados AS tecnico 
            ON reportes_fallas.tecnico = tecnico.empleado_id
            LEFT JOIN empleados AS finalizacion 
            ON reportes_fallas.usuario_finalizacion = finalizacion.usuario_id
            WHERE YEAR(reportes_fallas.fecha_captura) = YEAR(CURRENT_DATE)
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function get_priorities() 
    {
        $query = Flight::gnconn()->prepare("SELECT * FROM prioridad_reportes");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    public function common_failures() 
    {
        $query = Flight::gnconn()->prepare("SELECT * FROM reportes_comunes");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }

    private function get_customer_mikrotik_data($cliente_id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.cliente_id, 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, 
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
     * insert_into_mikrotik_retry
     * 
     * Guardar los datos que no pudieron guardarse en el mikrotik para agregarlos despues
     * @param  mixed $cliente_id  Solo se necesita el id del cliente
     * @return void
     */
    public function insert_into_mikrotik_retry_add($cliente_id, $mikrotik_id, $server, $interface_arp, $profile, $module)
    {
        $query = Flight::gnconn()->prepare("INSERT INTO mikrotik_retry_add VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->execute([ $cliente_id, $mikrotik_id, $server,  $interface_arp,  $profile, $module ]);
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
        $is_exists = $this->this_record_already_exists('mikrotik_retry_add', $cliente_id, $module);
        if (!$is_exists) {
            $this->insert_into_mikrotik_retry_add($cliente_id, $mikrotik_id, $server, $interface_arp, $profile, $module);
        }
    }
    

    /**
     * save_customer_in_mikrotik          Agrega los datos del cliente al mikrotik
     * 
     * @return void
     **/
    public function save_customer_in_mikrotik($previus = [])
    {
        $rows = $this->get_customer_mikrotik_data($this->cliente_id);
        $ip = $rows[0]["mikrotik_ip"];
        $user = $rows[0]["mikrotik_usuario"];
        $password = $rows[0]["mikrotik_password"];
        $port = $rows[0]["mikrotik_puerto"];
        $cliente_nombres = $rows[0]["nombres"];
        $cliente_id = $rows[0]['cliente_id'];
        $mikrotik_id = $rows[0]['mikrotik_id'];
        $server = $rows[0]['server'];
        $interface = $rows[0]['interface_arp'];
        $profile =  $rows[0]['profile'];
        $metodo =  $rows[0]['metodo_bloqueo'];
        $cliente_ip = $rows[0]['cliente_ip'];
        $cliente_mac = $rows[0]['cliente_mac'];
        $banda = $rows[0]["ancho_banda"];
        $parent_queue = $rows[0]["parent_queue"];
        $name_secret = $rows[0]["user_pppoe"];
        $password_secret = $rows[0]["password_pppoe"];
        $conn = new Mikrotik($ip, $user, $password, $port);

        if (!$conn->connected) {
            $this->customer_fail_mikrotik($cliente_id, $mikrotik_id, $server, $interface, $profile, "all");
            $this->firewall = false;
            $this->queues = false;
            $this->leases = false;
            $this->arp = false;
            $this->pppoe = false;
        }

        if ($conn->connected) {
            switch ($metodo) {
                case 'DHCP':
                    $this->leases = $conn->add_from_dhcp_leases( $cliente_nombres, $cliente_ip, $cliente_mac, $server);
                    $this->firewall = $conn->add_from_address_list( $cliente_nombres, $cliente_ip, "ACTIVE" );
                    $this->queues = $conn->add_from_queue_simple($cliente_nombres, $cliente_ip, $banda, $parent_queue);
                    if ($rows[0]['cliente_status'] != 2 && $rows[0]['cliente_status'] != 3) {
                        $conn->remove_from_address_list($cliente_ip, "MOROSOS");
                        $conn->remove_from_filter_rules($cliente_mac);
                    }
                break;

                case 'ARP':
                    $this->arp = $conn->add_from_arp_list($cliente_ip, $cliente_mac, $cliente_nombres, $interface);
                    $this->firewall = $conn->add_from_address_list($cliente_nombres, $cliente_ip, "ACTIVE");
                    $this->queues = $conn->add_from_queue_simple( $cliente_nombres, $cliente_ip, $banda, $parent_queue);
                    if ($rows[0]['cliente_status'] != 2 && $rows[0]['cliente_status'] != 3) {
                        $conn->remove_from_address_list($cliente_ip, "MOROSOS");
                        $conn->remove_from_filter_rules($cliente_mac);
                    }
                break;

                case 'PPPOE':
                    $conn->remove_from_address_list( $cliente_ip, "MOROSOS" );
                    $conn->remove_from_address_list( $cliente_ip, "ACTIVE" );
                    $conn->remove_customer_queue( $cliente_ip);
                    $conn->remove_from_arp_list( $cliente_ip, $cliente_mac);
                    $conn->remove_from_dhcp_leases( $cliente_ip, $cliente_mac);
                    $disabled = $rows[0]['cliente_status'] == 2 ? "true" : "false";
                    $this->pppoe = $conn->add_from_secrets($name_secret, $password_secret, $profile, $cliente_ip, $disabled);
                break;
                default: // Mostrar nada
            }
        }
    }
}