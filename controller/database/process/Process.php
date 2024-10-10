<?php

class Process extends Messenger
{

    public $negociation;
    public $freemonth;
    public $automatical_suspencion;
    public $alerts_suspended;
    public $suspended_negociation;
    public $suspender_autorizacion;

    public $error;
    public $error_message;
    public $suspension;
    public $morosos;


    public function __construct()
    {
        $this->config = parse_ini_file('./config.ini', true);
    }


    /**
     * testWhatsapp
     *
     * @return mixed
     */
    public function testWhatsapp($usuario_id)
    {
        $phone = $this->get_brand();
        $phone = $phone[0]["codigo_pais"] . $phone[0]["phone"];
        $array = [ "phone" => $phone, "message" => "$usuario_id: Connected" ];
        return $this->whatsapp($array, $usuario_id, 'test');
    }


    /**
     * finish_free_month
     *
     * @return mixed
     */
    public function finish_free_month()
    {
        $is_executed = $this->pending_process('free_month');
        if (!$is_executed) return;
        
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios 
                JOIN clientes ON clientes_servicios.cliente_id = clientes.cliente_id
                SET clientes_servicios.cliente_status = 1 
                WHERE DATEDIFF(CURRENT_DATE(), clientes.cliente_instalacion) >= 30
                AND clientes_servicios.cliente_status = 6
            ");
            $query->execute();
            $this->end_process('free_month');
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al finalizar el mes gratis";
        }
    }


    /**
     * layoff_customers
     *
     * @return mixed
    **/
    public function layoff_customers()
    {
        $is_pending = $this->pending_process('suspension');

        /**
         * Virificar que el proceso no se encuentre pendiente
         * Si no esta pendiente entonces ya se realizo
        **/
        if (!$is_pending) {
            $this->suspension = true;
            return true;
        }

        /**
         * Verificar los pagos en status autorizacion
         * Y asignar el status al cliente si esta activo, suspendido, negociacion, nuevo
        **/
        $this->verify_payment_in_autorization();
        if ($this->error) return;

        /**
         * Obtener la lista de cortes de servicios
         * Para seleccionar a los clientes que se van a suspender
         * en base a los dias de inicio y finalizacion de corte
        **/
        $cuts = $this->get_cuts_today_finally();

        /**
         * Detectar el periodo de pago
         * en base a los dias de pago de los cortes de servicios
         * 
         * Para que el periodo sea el del mes anterior el dia de pago debe estar en el rango de dias finales
         * Y el dia de suspencion del cliente debe estar en el rango de dias iniciales, es decir, si el cliente paga los dias 30
         * Y su dia de suspencion es 3, el periodo sera el del mes anterior
         * 
         * Para que el pago sea del periodo actual:
         * El dia de pago y el de suspension deben estar en el rango de dias iniciales
         * O bien el dia de pago y de suspencion deben estar en el rango de dias finales
        */
        
        $dias_inicio = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17];
        $dias_finales = [18,19,20,21,22,23,24,25,26,27,28,29,30,31];

        foreach ($cuts as $cortes) {
            $is_before = in_array($cortes['dia_pago'], $dias_finales) 
                && in_array($cortes['dia_terminacion'], $dias_inicio);

            $is_current = in_array($cortes['dia_pago'], $dias_inicio) 
                && in_array($cortes['dia_terminacion'], $dias_inicio)
                || in_array($cortes['dia_pago'], $dias_finales) 
                && in_array($cortes['dia_terminacion'], $dias_finales);

            $periods = $is_before ? $this->get_before_periods() : $this->get_to_periods();
            $this->layoff_in_database($periods, $cortes["dia_pago"]);
        }

        $this->end_process('suspension');
        $this->suspension = true;
        return true;
    }



    /**
     * process_negociation
     * 
     * Comenzar el proceso de inicio y finalizacion de negociaciones
     * Primero se inician las negociaciones que inician mañana
     * Y luego se finalizan las que finalizan hoy
     *
     * @return void
     */
    public function process_negociation()
    {
        $is_pending = $this->pending_process('negociacion');
        if (!$is_pending) return;

        /**
         * Si hay alguno en negociacion y que ya pago
         * Finalizar la negociacion y cambiar el status a activo
        **/
        $this->activate_customer();
        if ($this->error) return;

        /**
         * 
         * Comenzar las negociaciones que inician mañana
         * Para que los clientes no se suspendan
         * 
        **/
        $this->start_negociations();
        if ($this->error) return;
        
        /**
         * 
         * Si todo sale bien con el inicio de las negociaciones continuamos con el proceso
         * Y finalizamos las negociaciones que finalizan el dia de hoy
         * 
         */
        $customers = $this->clients_with_completed_trading();
        
        /**
         * @var mixed
         * Obtener el template de negociacion terminada
         */
        $template = $this->get_templates('negociacion_terminada');

        /**
         * @var mixed
         * Obtener los datos de la empresa
         */
        $brand = $this->get_brand();

        $suspended = "";

        if (!empty($customers)) {
            $this->suspended_negociation = $this->get_config_status(4);

            foreach ($customers as $customer) {
                $nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];
                $message = preg_replace(['{{cliente}}', '{{fecha_fin}}'], [$nombres, $customer["fecha_fin"]], $template[0]["template"]);
                $data = [ "phone" => $brand[0]["codigo_pais"].$customer["cliente_telefono"], "message" => $message ];
                $this->whatsapp($data, $customer['cliente_id'], 'end_negociation');
                $suspended .= "$nombres : ".$customer["nombre_colonia"]."\n";

                /**
                 * @var mixed
                 * Deshabilitar el servicio del cliente
                **/
                $this->disabled_service($customer);
            }

            /**
             * @var mixed
             * Finalizar las negociaciones
            **/
            $this->finalize_negociations();

            /**
             * @var array
             * Generar el mensaje para el administrador
             */
            $params = array(
                "phone" => $brand[0]["codigo_pais"].$brand[0]["phone"], 
                "message" => "*Negociaciones suspendidas*\n\n".$suspended
            );
            
            /**
             * Enviar un mensaje a los administradores
             * De que se han suspendido las negociaciones
             **/
            $this->whatsapp($params, 'ADMIN', 'negociation_finally');
        }

        /**
         * Finalizar el proceso de negociaciones
        **/
        $this->end_process('negociacion');
    }


    /**
     * change_priority_failures
     * 
     * Cambiar la prioridad de las fallas si la fecha de atencion es mañana
     *
     * @return void
     */
    public function change_priority_failures() : void
    {
        /**
         * Verificar que el proceso no se encuentre pendiente
         * Si no esta pendiente entonces ya se realizo
        **/
        $is_pending = $this->pending_process('failures_priority');
        if (!$is_pending) return;

        /**
         * Actualizar la prioridad de las fallas
        **/
        $this->update_priority_failures();

        /**
         * Finalizar el proceso
        **/
        $this->end_process('failures_priority');
    }


    /**
     * mikrotik_backup
     * 
     * Realizar el backup de los mikrotiks
     * 
     * @return void
    **/
    public function mikrotik_backup()
    {
        /**
         * Verificar que el proceso no se encuentre pendiente
         * Si no esta pendiente entonces ya se realizo
        **/
        $is_pending = $this->pending_process('mikrotik_backup');
        if (!$is_pending) return;

        /**
         * Obtener los mikrotiks activos
        **/
        $mikrotiks = $this->get_mikrotiks_enabled();

        /**
         * Realizar el backup de cada mikrotik
        **/
        foreach($mikrotiks as $mikrotik) {
            $address = $mikrotik['mikrotik_ip'];
            $usuario = $mikrotik['mikrotik_usuario'];
            $password = $mikrotik['mikrotik_password'];
            $puerto = $mikrotik['mikrotik_puerto'];

            $conn = new Mikrotik($address, $usuario, $password, $puerto);

            /**
             * Si no se puede conectar al mikrotik, se salta el backup
            **/
            if (!$conn->connected) continue;

            /**
             * Realizar el backup del mikrotik
            **/
            if ($conn->connected) {
                $conn->comm("/system/backup/save", ["name" => "Backup-" . date("Y-m-d:h_m:s")]);
                $conn->disconnect();
            }
        }

        /**
         * Finalizar el proceso
        **/
        $this->end_process('mikrotik_backup');
    }


    /**
     * send_suspension_messages
     * 
     * Envía mensajes de suspensión a los clientes que han sido suspendidos el día de hoy.
     * 
     * Este método realiza las siguientes acciones:
     * 1. Verifica si el proceso de alerta de suspensión está pendiente.
     * 2. Obtiene la lista de clientes suspendidos el día actual.
     * 3. Recupera la plantilla de mensaje de suspensión.
     * 4. Obtiene los datos de la empresa.
     * 5. Para cada cliente suspendido:
     *    - Personaliza el mensaje con los datos del cliente.
     *    - Envía el mensaje de WhatsApp utilizando la función 'whatsapp'.
     * 6. Maneja errores si no se puede enviar algún mensaje.
     * 7. Finaliza el proceso de alerta de suspensión (implícitamente, ya que no se muestra en el código proporcionado).
     * 
     * @return void
     * @throws Exception Si no se pueden enviar los mensajes de suspensión.
     */
    public function send_suspension_messages()
    {
        /**
         * Verificar que el proceso no se encuentre pendiente
         * Si no esta pendiente entonces ya se realizo
        **/
        $is_pending = $this->pending_process('alert_suspension');
        if (!$is_pending) return;

        /**
         * 
         * Obtener los clientes suspendidos
         * Y que se supendieron el dia de hoy
         * 
        **/
        $customers = $this->get_current_suspended();

        /**
         * Obtener el template de suspencion
        **/
        $template = $this->get_templates('suspension');

        /**
         * Obtener la informacion de la empresa
        **/
        $brand = $this->get_brand();

        $list = "";

        /**
         * 
         * Enviar los mensajes de suspension a los clientes
         * Que fueron suspendidos en el dia de hoy
         * 
         */
        foreach ($customers as $customer) {
            $phone = $customer["cliente_telefono"];
            $nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];
            $colonia = $customer["nombre_colonia"];
            $corte = $customer["cliente_corte"];

            $message = preg_replace( ['{{cliente}}', '{{corte}}'], [$nombres, $corte], $template[0]["template"] );
            $phone = $brand[0]["codigo_pais"].$phone;
            $options = ["phone" => $phone, "message" => $message];

            $whatsapp = $this->whatsapp( $options, $customer['cliente_id'], 'suspension' );

            if (!$whatsapp) {
                $this->error = true;
                $this->error_message = "Error al enviar el mensaje de suspencion a $nombres";
                return;
            }

            $list .= "$nombres : $colonia\n";
        }
        
        if (!empty($list)) {
            $brand_phone = $brand[0]["codigo_pais"].$brand[0]["phone"];
            $params = array("phone" => $brand_phone, "message" => "*Clientes suspendidos*\n\n".$list);
            $this->whatsapp($params, 'ADMIN', 'suspension');
        }

        $this->end_process('alert_suspension');
    }


    /**
     * send_reminder_message
     * 
     * Envía mensajes de recordatorio de pago a los clientes.
     * 
     * Este método realiza las siguientes acciones:
     * 1. Verifica si el proceso de alerta de pago está pendiente.
     * 2. Comprueba si los mensajes de alerta están habilitados en la configuración.
     * 3. Obtiene los cortes de servicio que inician hoy.
     * 4. Define los rangos de días para el inicio y final del período de pago.
     * 5. Para cada corte de servicio:
     *    - Determina si el período de pago es posterior o actual.
     *    - Obtiene los períodos correspondientes.
     *    - Envía los mensajes de recordatorio a los clientes que cumplen las condiciones.
     * 6. Finaliza el proceso de alerta de pago.
     * 
     * @return void
     */
    public function send_reminder_message()
    {
        /**
         * @var mixed
         * Verificar que el proceso no se encuentre pendiente
         * Si no esta pendiente entonces ya se realizo
        **/
        $is_pending = $this->pending_process("alert_payment");
        if (!$is_pending) return;

        /**
         * Los mensajes de alerta estan habilitados?
        **/
        $alert_previus_payment = $this->get_config_status(7);
        if ($alert_previus_payment == 0) return;

        /** 
         * Obtener los cortes que inicien que inician hoy 
        **/
        $cuts = $this->get_cuts_today_start();

        /**
         * Crear los dias finales y dias restantes
         * Para compararlos con el dia actual
        **/
        $dias_inicio = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17];
        $dias_finales = [18,19,20,21,22,23,24,25,26,27,28,29,30,31];

        /**
         * Comenzar a recorrer los cortes 
         * Y enviar los mensajes de alerta de pago
         */
        foreach ($cuts as $cortes) {
            $is_after = in_array($cortes['dia_comienzo'], $dias_finales) 
                && in_array($cortes['dia_pago'], $dias_inicio);

            $is_current = in_array($cortes['dia_pago'], $dias_inicio) 
                && in_array($cortes['dia_terminacion'], $dias_inicio)
                || in_array($cortes['dia_pago'], $dias_finales) 
                && in_array($cortes['dia_terminacion'], $dias_finales);

            $periods = $is_after ? $this->get_after_periods() : $this->get_to_periods();

            /**
             * Enviar el mensaje de alerta de pago
             * A los clientes que cumplan con las condiciones
            **/
            $this->send_reminder_whatsapp($periods);
        }

        /**
         * Finalizar el proceso
        **/
        $this->end_process("alert_payment");
    }


    /**
     * send_billing_messages
     * 
     * Envía mensajes de facturación a los clientes que deben realizar un pago.
     * 
     * Este método realiza las siguientes acciones:
     * 1. Verifica si la configuración de facturación está habilitada.
     * 2. Comprueba si el proceso de alerta de facturación está pendiente.
     * 3. Obtiene la lista de clientes que deben realizar un pago.
     * 4. Recupera la plantilla de mensaje de facturación.
     * 5. Obtiene los datos de la empresa.
     * 6. Para cada cliente:
     *    - Personaliza el mensaje con los datos del cliente.
     *    - Envía el mensaje de WhatsApp utilizando la función 'whatsapp'.
     * 7. Maneja errores si no se puede enviar algún mensaje.
     * 8. Finaliza el proceso de alerta de facturación.
     * 
     * @return void
     * @throws Exception Si no se pueden enviar los mensajes de facturación.
     */
    public function send_billing_messages()
    {
        /**
         * Obtener el estado de la configuracion de facturacion
        **/
        $config_billing = $this->get_config_status(8);
        if ($config_billing == 0) return;

        /**
         * Verificar que el proceso no se encuentre pendiente
         * Si no esta pendiente entonces ya se realizo
        **/
        $last = $this->pending_process("alert_billing");
        if (!$last) return;

        /**
         * Obtener los clientes que tienen que pagar
        **/ 
        $customers = $this->get_customers_in_billing();

        /**
         * Obtener el template de facturacion
        **/
        $template = $this->get_templates('facturacion');

        /**
         * Obtener los datos de la empresa
        **/
        $brand = $this->get_brand();

        /**
         * Enviar los mensajes de facturacion
        **/
        foreach ($customers as $customer) {
            $phone = $brand[0]['codigo_pais'].$customer["cliente_telefono"];
            $nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];
            $message = preg_replace(['{{cliente}}'], [$nombres], $template[0]["template"]);

            /**
             * Mensaje de facturacion
            **/
            $data = array("phone" => $phone, "message" => $message);

            /**
             * Enviar el mensaje de facturacion
            **/
            $whatsapp = $this->whatsapp($data, $customer['cliente_id'], 'billing');

            if (!$whatsapp) {
                $this->error = true;
                $this->error_message = "No se pudo enviar los mensajes de facturacion";
                return;
            }
        }

        /**
         * Finalizar el proceso
        **/
        $this->end_process("alert_billing");
    }


    /**
     * add_morosos
     * 
     * Agregar a los clientes que ya deben ser considerados morosos
     * 
     * @return void
    **/
    public function add_morosos()
    {
        /**
         * Verificar que el proceso no se encuentre pendiente
         * Si no esta pendiente entonces ya se realizo
        **/
        $is_pending = $this->pending_process('add_morosos');
        if (!$is_pending) return;

        /**
         * Obtener los clientes que ya deben ser considerados morosos
         * Y que no tengan un pago pendiente
         * Y que se hayan suspendido hoy
        **/
        $customers = $this->get_current_suspended();
        if (empty($customers)) return;

        /**
         * Deshabilitar los servicios de los clientes
        **/
        foreach ($customers as $customer) {
            $disabled = $this->disabled_service($customer);
            if (!$disabled) {
                $this->error = true;
                $this->error_message = "Error al deshabilitar el servicio del cliente";
                return;
            }
        }
    }


    /**
     * layoff_in_database
     * 
     * Suspende los servicios de los clientes en la base de datos según ciertos criterios.
     * 
     * Esta función realiza las siguientes acciones:
     * 1. Verifica la configuración del sistema para determinar si se debe suspender con autorización.
     * 2. Actualiza el estado de los servicios de los clientes a suspendido (2) si:
     *    - No existen pagos para los períodos especificados con estado pagado o en proceso.
     *    - El estado actual del servicio cumple con los criterios establecidos.
     *    - El cliente pertenece al corte de servicio especificado.
     *    - El cliente tiene un paquete asignado (no es 0).
     *    - El tipo de cliente es 1.
     *    - La opción de suspender está activada para el cliente.
     * 3. Actualiza la fecha de última suspensión del cliente.
     * 
     * @param array $periods Array de períodos para verificar pagos.
     * @param int $corte_id ID del corte de servicio.
     * 
     * @throws Exception Si ocurre un error durante la ejecución de la consulta SQL.
     * 
     * @return void
    **/
    public function layoff_in_database($periods, $corte_id)
    {
        // Obtener el estado de la configuracion de suspencion en autorizacion
        $this->suspender_autorizacion = $this->get_config_status(6);

        // Solo suspender si esta activa la opcion en la configuracion del sistema 
        $status = $this->suspender_autorizacion == 1 ? [1,5] : [1];

        // Solo suspender si esta activa la opcion en la configuracion del sistema 
        $IN_PERIODS = $this->convert_to_string($periods);  // String SQL (IN)
        
        // Convertir el array de status a un string SQL (IN)
        $IN_STATUS = $this->convert_to_string($status);

        try {
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios 
                SET cliente_status = 2,
                ultima_suspension = CURRENT_TIMESTAMP()
                WHERE NOT EXISTS(
                    SELECT pagos.pago_id FROM pagos 
                    WHERE pagos.cliente_id = clientes_servicios.cliente_id 
                    AND pagos.periodo_id IN $IN_PERIODS
                    AND pagos.status_pago IN (1,2)
                ) 
                AND cliente_status IN $IN_STATUS
                AND cliente_corte = $corte_id
                AND cliente_paquete != 0
                AND cliente_tipo = 1
                AND suspender = 1
            ");
            $query->execute();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al suspender en la base de datos";
        }
    }


    public function verify_payment_in_autorization()
    {
        try {
            $periodos = $this->get_to_periods();
            $periodo_actual = $this->convert_to_string($periodos);

            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios
                JOIN pagos
                ON clientes_servicios.cliente_id = pagos.cliente_id
                SET clientes_servicios.cliente_status = 5
                WHERE clientes_servicios.cliente_corte = DAY(DATE_ADD(CURRENT_DATE, INTERVAL +1 DAY))
                AND pagos.status_pago = 2
                AND pagos.periodo_id IN $periodo_actual
                AND clientes_servicios.cliente_status IN(1,2,4,6)
            ");
            $query->execute();
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al iniciar las negociaciones";
        }
    }

   
    /**
     * activate_customer
     * 
     * Activa el cliente
     * 
     * Esta función activa el estado del cliente y las negociaciones
     * cuando la fecha de inicio de la negociación es igual a la fecha actual o al día siguiente.
     * 
     * Actualiza:
     * - El estado de la negociación a 3 (activa)
     * - El estado del cliente a 1 (activo)
     * 
     * Condiciones:
     * - La fecha de inicio de la negociación es igual a la fecha actual o al día siguiente
     * - El estado actual de la negociación es 1 (iniciada)
     * 
     * @return void
     * @throws Exception Si ocurre un error al activar el cliente
    */
    public function activate_customer()
    {
        try {
            $periodos = $this->get_to_periods();
            $periodo_actual = $this->convert_to_string($periodos);

            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios
                JOIN negociaciones
                ON clientes_servicios.cliente_id = negociaciones.cliente_id
                SET negociaciones.status_negociacion = 3,
                clientes_servicios.cliente_status = 1
                WHERE DATE_ADD(CURRENT_DATE, INTERVAL +1 DAY) = DATE(negociaciones.fecha_fin)
                OR DATE(CURRENT_DATE()) >= DATE(negociaciones.fecha_fin)
                AND negociaciones.status_negociacion = 1
                AND EXISTS (
                    SELECT pagos.pago_id FROM pagos
                    WHERE pagos.periodo_id IN $periodo_actual
                    AND pagos.cliente_id = negociaciones.cliente_id
                );
            ");
            $query->execute();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al iniciar las negociaciones";
        }
    }


    /**
     * start_negociations
     * 
     * Inicia las negociaciones para los clientes
     * 
     * Esta función actualiza el estado de las negociaciones y los servicios de los clientes
     * cuando la fecha de inicio de la negociación es igual a la fecha actual o al día siguiente.
     * 
     * Actualiza:
     * - El estado de la negociación a 1 (iniciada)
     * - El estado del servicio del cliente a 4 (en negociación)
     * 
     * Condiciones:
     * - La fecha de inicio de la negociación es igual a la fecha actual o al día siguiente
     * - El estado actual de la negociación es 2 (pendiente)
     * 
     * @return void
     * @throws Exception Si ocurre un error al iniciar las negociaciones
    */
    public function start_negociations()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE clientes_servicios
                JOIN negociaciones
                ON clientes_servicios.cliente_id = negociaciones.cliente_id
                SET negociaciones.status_negociacion = 1,
                clientes_servicios.cliente_status = 4
                WHERE DATE_ADD(CURRENT_DATE, INTERVAL +1 DAY) = DATE(negociaciones.fecha_inicio)
                OR DATE(CURRENT_DATE()) >= DATE(negociaciones.fecha_inicio)
                AND negociaciones.status_negociacion = 2
            ");
            $query->execute();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al iniciar las negociaciones";
        }
    }


    /**
     * 
     * Obtiene los clientes con negociaciones completadas
     *
     * Esta función recupera una lista de clientes cuyas negociaciones han finalizado
     * y que no han realizado pagos en los períodos especificados.
     *
     * @return array Un array con la información de los clientes que cumplen los criterios
     * 
    **/
    public function clients_with_completed_trading()
    {
        $periods = $this->get_to_periods();
        $IN_PERIODS = $this->convert_to_string($periods);

        $query = Flight::gnconn()->prepare("
            SELECT 
                negociaciones.id_negociacion,
                negociaciones.fecha_fin,
                clientes.cliente_id,
                clientes.cliente_nombres,
                clientes.cliente_apellidos,
                clientes.cliente_telefono,
                clientes.metodo_bloqueo,
                clientes.interface_arp,
                clientes.server,
                clientes.profile,
                clientes.user_pppoe,
                clientes.password_pppoe,
                clientes.cliente_ip,
                clientes_servicios.suspender,
                mikrotiks.mikrotik_ip,
                mikrotiks.mikrotik_usuario,
                mikrotiks.mikrotik_password,
                mikrotiks.mikrotik_puerto,
                mikrotiks.mikrotik_nombre,
                mikrotiks.mikrotik_id,
                colonias.nombre_colonia,
                colonias.colonia_id
            FROM clientes 
            LEFT JOIN negociaciones 
            ON clientes.cliente_id = negociaciones.cliente_id 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id
            INNER JOIN mikrotiks
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id
            WHERE DATE(negociaciones.fecha_fin) <= DATE(CURRENT_DATE())
            AND DATEDIFF(CURRENT_DATE(), negociaciones.fecha_fin) >= 0
            AND negociaciones.status_negociacion = 1
            AND clientes_servicios.cliente_status = 4
            AND NOT EXISTS ( 
                SELECT pagos.pago_id FROM pagos 
                WHERE pagos.cliente_id = clientes_servicios.cliente_id 
                AND pagos.periodo_id 
                IN $IN_PERIODS 
                AND pagos.status_pago IN(1,2)
            )
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * finalize_negociations
     * 
     * Finaliza las negociaciones que han llegado a su fecha límite y actualiza el estado de los clientes.
     * 
     * Este método realiza las siguientes acciones:
     * 1. Obtiene los períodos relevantes para la verificación de pagos.
     * 2. Actualiza el estado de las negociaciones a finalizado (3) y el estado del cliente a suspendido (2).
     * 3. Actualiza la fecha de última suspensión del cliente.
     * 4. Aplica estos cambios solo a las negociaciones que han alcanzado o superado su fecha de finalización.
     * 5. Verifica que no existan pagos pendientes o procesados para los períodos relevantes.
     * 
     * @throws Exception Si ocurre un error durante la ejecución de la consulta SQL.
     * 
     * @return void
    */
    public function finalize_negociations()
    {
        $periods = $this->get_to_periods();
        $string_periods = $this->convert_to_string($periods);

        try {
            $query = Flight::gnconn()->prepare("
                UPDATE negociaciones 
                JOIN clientes_servicios
                ON negociaciones.cliente_id = clientes_servicios.cliente_id
                SET negociaciones.status_negociacion = 3,
                clientes_servicios.cliente_status = 2,
                clientes_servicios.ultima_suspencion = CURRENT_TIMESTAMP()
                WHERE DATE(negociaciones.fecha_fin) <= DATE(CURRENT_DATE())
                AND DATEDIFF(CURRENT_DATE(), negociaciones.fecha_fin) >= 0
                AND negociaciones.status_negociacion = 1
                AND clientes_servicios.cliente_status IN(2,4)
                AND NOT EXISTS ( 
                    SELECT pagos.pago_id FROM pagos 
                    WHERE pagos.cliente_id = clientes_servicios.cliente_id 
                    AND pagos.periodo_id 
                    IN $string_periods 
                    AND pagos.status_pago IN(1,2)
                )
            ");
            $query->execute();
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al finalizar las negociaciones!";
        }
    }

    
    /**
     * update_priority_failures
     * 
     * Actualiza la prioridad de las fallas en el sistema.
     * 
     * Esta función realiza las siguientes acciones:
     * 1. Cambia la prioridad de las fallas a 1 (alta prioridad) si:
     *    - La fecha de atención es mañana.
     *    - El estado de la falla está en 1 o 2 (pendiente o en proceso).
     *    - La prioridad actual es 2 o 3 (media o baja).
     * 
     * @throws Exception Si ocurre un error durante la ejecución de la consulta SQL.
     * 
     * @return void
    **/
    public function update_priority_failures()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE reportes_fallas SET prioridad = 1
                WHERE DATE(fecha_atencion) = DATE_ADD(CURRENT_DATE(), INTERVAL +1 DAY)
                AND status IN (1,2)
                AND prioridad IN (2,3)
            ");
            $query->execute();
        } 
        catch (Exception $error) {
            $this->error = true;
            $this->error_message = "Error al verificar las fallas";
        }
    }


    /**
     * get_current_suspended
     * 
     * Obtiene los clientes que han sido suspendidos en el día actual.
     * 
     * Esta función realiza las siguientes acciones:
     * 1. Consulta la base de datos para obtener información de los clientes suspendidos.
     * 2. Filtra los clientes cuya fecha de suspensión coincide con la fecha actual.
     * 3. Incluye solo los clientes cuyo día de corte de servicio es hoy.
     * 4. Verifica que el estado del cliente sea suspendido (2) y que tenga activada la opción de suspender.
     * 
     * @return array Un array con la información de los clientes suspendidos que cumplen los criterios.
    **/
    public function get_current_suspended()
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.cliente_id, 
                clientes.cliente_ip,
                clientes.cliente_mac,
                clientes.interface_arp,
                clientes.profile,
                clientes.server,
                clientes.user_pppoe,
                clientes.password_pppoe,
                clientes.cliente_nombres,
                clientes.cliente_apellidos, 
                clientes.cliente_telefono, 
                clientes.metodo_bloqueo,
                clientes_servicios.cliente_corte, 
                clientes_servicios.ultima_suspension,
                colonias.nombre_colonia,
                cortes_servicio.dia_terminacion,
                mikrotiks.mikrotik_ip,
                mikrotiks.mikrotik_usuario,
                mikrotiks.mikrotik_password,
                mikrotiks.mikrotik_puerto,
                mikrotiks.mikrotik_nombre,
                mikrotiks.mikrotik_id
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id 
            INNER JOIN mikrotiks
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id
            INNER JOIN cortes_servicio 
            ON clientes_servicios.cliente_corte = cortes_servicio.corte_id 
            WHERE DATEDIFF(CURRENT_DATE(), clientes_servicios.ultima_suspension) BETWEEN 0 AND 3
            AND clientes_servicios.cliente_status = 2
            AND clientes_servicios.suspender = 1
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * send_reminder_whatsapp
     * 
     * Envía mensajes de recordatorio de pago a través de WhatsApp a los clientes.
     * 
     * Esta función realiza las siguientes acciones:
     * 1. Obtiene la lista de clientes que deben recibir una alerta de pago.
     * 2. Recupera la plantilla de mensaje para las alertas.
     * 3. Obtiene los datos de la marca o empresa.
     * 4. Para cada cliente en la lista:
     *    - Construye el número de teléfono completo.
     *    - Genera el nombre completo del cliente.
     *    - Personaliza el mensaje de la plantilla con los datos del cliente y el período.
     *    - Prepara los datos para el envío del mensaje.
     *    - Envía el mensaje de WhatsApp utilizando la función 'whatsapp'.
     * 
     * @param array $periods Array de períodos para los cuales se envían las alertas.
     * 
     * @return void
    **/
    public function send_reminder_whatsapp($periods)
    {
        /**
         * Obtener los clientes que tienen que pagar
        **/     
        $customers = $this->get_customers_in_alert($periods);

        /**
         * Obtener el template de alertas
        **/
        $template = $this->get_templates('alertas');

        /**
         * Obtener los datos de la empresa
        **/
        $brand = $this->get_brand();

        /**
         * Enviar los mensajes de alertas
        **/
        foreach ($customers as $customer) {
            $phone = $brand[0]["codigo_pais"].$customer["cliente_telefono"];

            $nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];

            $periodo = $periods[0];

            $message = preg_replace(['{{cliente}}', '{{periodos}}'], [$nombres, $periodo], $template[0]["template"]);

            $data = array("phone" => $phone, "message" => $message);

            /**
             * Enviar el mensaje de alerta
            **/
            $whatsapp = $this->whatsapp($data, $customer['cliente_id'], 'reminder');

            if (!$whatsapp) {
                $this->error = true;
                $this->error_message = "No se pudo enviar los mensajes de recordatorio";
                return;
            }
        }
    }


    /**
     * get_customers_in_alert
     * 
     * Obtener lo clientes que tienen que pagar en los proximos dias
     *
     * @param  mixed $periods
     * @return array
     * 
    **/
    public function get_customers_in_alert($periods)
    {
        $string_periods = $this->convert_to_string($periods);
        $query = Flight::gnconn()->prepare("
            SELECT 
                clientes.cliente_id, 
                clientes.cliente_nombres,
                clientes.cliente_apellidos, 
                clientes.cliente_telefono, 
                clientes_servicios.cliente_corte, 
                cortes_servicio.dia_pago 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN cortes_servicio 
            ON clientes_servicios.cliente_corte = cortes_servicio.corte_id 
            WHERE NOT EXISTS (
                SELECT pagos.pago_id FROM pagos 
                WHERE pagos.cliente_id = clientes.cliente_id 
                AND pagos.periodo_id IN $string_periods
            ) 
            AND cortes_servicio.dia_comienzo <= DAY(CURRENT_DATE) 
            AND cortes_servicio.dia_pago >= DAY(CURRENT_DATE)
            AND clientes_servicios.cliente_paquete != 0
            AND clientes_servicios.suspender != 0
            AND clientes_servicios.cliente_status IN (1,2,4,5,6)
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * get_customers_in_billing
     * 
     * Obtener los clientes que tienen que pagar en los proximos dias
     *
     * @param  mixed $periods
     * @return array
     * 
    **/
    public function get_customers_in_billing()
    {
        $periods = $this->get_to_periods();
        $string_periods = $this->convert_to_string($periods);

        $SQL = "
            SELECT 
                clientes.cliente_id,
                clientes.cliente_nombres,
                clientes.cliente_apellidos, 
                clientes.cliente_telefono, 
                clientes_servicios.cliente_corte, 
                cortes_servicio.dia_pago 
            FROM clientes 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN cortes_servicio 
            ON clientes_servicios.cliente_corte = cortes_servicio.corte_id 
            WHERE NOT EXISTS(
                SELECT pagos.pago_id FROM pagos 
                WHERE pagos.cliente_id = clientes.cliente_id 
                AND pagos.periodo_id IN $string_periods 
                AND pagos.status_pago IN(1,2)
            )
            AND DAY(CURRENT_DATE) - clientes_servicios.cliente_corte BETWEEN 0 AND 3
            AND clientes_servicios.cliente_status IN (1,2,4,5,6)
            AND clientes_servicios.cliente_paquete != 0
            AND clientes_servicios.suspender != 0
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * 
     * get_cuts_today_finally
     * Obtener los cortes de servicio que finalizan en el dia actual
     * 
     * @return array
     */
    public function get_cuts_today_finally()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM cortes_servicio 
            WHERE dia_terminacion <= DAY(CURRENT_DATE)
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * get_before_periods
     * 
     * Global function to get the previous period
     * 
     * @return array
     * 
    **/
    public function get_before_periods()
    {
        $query = Flight::gnconn()->prepare("
            SELECT DATE(
                DATE_ADD(
                    CURRENT_DATE, 
                    INTERVAL -1 MONTH
                )
            ) AS before_date
        ");
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[ 0 ][ "before_date" ]);
        $periodo_anterior = $fecha[ 1 ] . $fecha[ 0 ];
        $periodos = array($periodo_anterior);
        return $periodos;
    }


    /**
     * 
     * get_to_periods
     *
     * Global function to get the current period
     * 
     * @return array
     * 
    **/
    public function get_to_periods()
    {
        $query = Flight::gnconn()->prepare("
            SELECT DATE(CURRENT_DATE) AS to_date
        ");
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[0]["to_date"]);
        $periodo_actual = $fecha[1] . $fecha[0];
        $periodos = array($periodo_actual);
        return $periodos;
    }


    /**
     * get_after_periods
     * 
     * Global function to get the next period
     * 
     * @return array
     * 
    **/
    public function get_after_periods()
    {
        $SQL = "SELECT DATE(DATE_ADD(CURRENT_DATE, INTERVAL +1 MONTH)) AS after_date";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        $fecha = explode("-", $rows[ 0 ][ "after_date" ]);
        $periodo_posterior = $fecha[ 1 ] . $fecha[ 0 ];
        $periodos = array($periodo_posterior);
        return $periodos;
    }


    /**
     * get_cuts_today_start
     * 
     * Obtiene los cortes de servicio que comienzan en el día actual.
     * 
     * @return array
    **/
    public function get_cuts_today_start()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM cortes_servicio 
            WHERE dia_comienzo = DAY(CURRENT_DATE)
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * pending_process
     *
     * Global function to check if the process is pending
     * 
     * @param mixed $module
     * @return mixed
     * 
    **/
    public function pending_process($module)
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM process_db 
            WHERE module = ? 
            AND DATE(last_send) = CURRENT_DATE
        ");
        $query->execute([ $module ]);
        $rows = $query->fetchAll();
        $is_pending = empty($rows);
        return $is_pending;
    }


    /**
     * end_process
     *
     * @param  mixed $module
     * @return mixed
     */
    public function end_process($module)
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE process_db 
                SET last_send = CURRENT_DATE 
                WHERE module = ?
            ");
            $query->execute([ $module ]);
        } catch (Exception $e) {
            $this->error = true;
            $this->error_message = $e->getMessage();
        }
    }


    /**
     * convert_to_string
     * 
     * Convertir array a sentencia IN de SQL
     * 
     * @param  mixed $periods
     * @return string
     */
    public function convert_to_string($periods)
    {
        $text = json_encode($periods);
        $order = array('"', '[', ']');
        $replace = array("'", "(", ")");
        $in = str_replace($order, $replace, $text);
        return $in;
    }


    /**
     * get_config_status
     * 
     * Global function to get the status of a configuration
     * 
     * @param  mixed $id
     * @return number
     * 
    **/
    public function get_config_status($id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT status 
            FROM configuration 
            WHERE id = ?
        ");
        $query->execute([ $id ]);
        $rows = $query->fetchAll();
        return $rows[0]["status"];
    }


    /**
     * get_mikrotiks_enabled
     * 
     * Obtiene todos los mikrotiks activos
     * 
     * @return array
     * 
    **/
    public function get_mikrotiks_enabled()
    {
        $SQL = "
            SELECT 
                mikrotik_ip,
                mikrotik_usuario,
                mikrotik_password,
                mikrotik_puerto
            FROM mikrotiks
            WHERE mikrotik_status = 1
        ";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * Deshabilita el servicio de un cliente en el Mikrotik
     *
     * Esta función se encarga de deshabilitar el servicio de un cliente específico
     * en el router Mikrotik correspondiente. Utiliza diferentes métodos de bloqueo
     * según la configuración del cliente (DHCP, ARP, PPPOE).
     *
     * @param array $customer Arreglo con la información del cliente y del Mikrotik
     * Debe contener: cliente_nombres, cliente_apellidos, mikrotik_ip,
     * mikrotik_usuario, mikrotik_password, mikrotik_puerto,
     * mikrotik_nombre, metodo_bloqueo, cliente_ip, user_pppoe, profile
     *
     * @return bool|void Retorna false si hay un error de conexión, void en caso contrario
     *
     * @throws Exception Si ocurre un error durante la conexión o deshabilitación del servicio
     */
    public function disabled_service($customer) {
        $cliente_nombres = $customer["cliente_nombres"]." ".$customer["cliente_apellidos"];

        $conn = new Mikrotik(
            $customer["mikrotik_ip"],
            $customer["mikrotik_usuario"],
            $customer["mikrotik_password"],
            $customer["mikrotik_puerto"]
        );

        /**
         * Verificar que la conexion sea exitosa
        **/
        if (!$conn->connected) {
            $this->error = true;
            $this->error_message = "Mikrotik error " . $customer["mikrotik_nombre"];
            $this->morosos = false;
            return false;
        }

        /**
         * Deshabilitar el servicio del cliente
        **/
        if ($conn->connected) {
            switch($customer['metodo_bloqueo']) {
                case 'DHCP':
                    $conn->add_from_address_list(
                        $cliente_nombres,
                        $customer['cliente_ip'],
                        "MOROSOS"
                    );
                    $conn->disconnect();
                break;
    
                case 'ARP':
                    $conn->add_from_address_list(
                        $cliente_nombres,
                        $customer['cliente_ip'],
                        "MOROSOS"
                    );
                    $conn->disconnect();
                break;
    
                case 'PPPOE':
                    $conn->disabled_secret(
                        $customer['user_pppoe'],
                        $customer['profile']
                    );
    
                    $this->morosos = $conn->remove_customer_actives(
                        $customer["user_pppoe"],
                        $customer['profile']
                    );
                    $conn->disconnect();
                break;
                default: // NO HACER NADA
            }
        }
    }
}
