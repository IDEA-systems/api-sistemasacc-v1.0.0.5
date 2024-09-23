<?php

class Retry extends Messenger
{

    public function __construct() {}

    
    public function retry_send_whatsapp()
    {
        $this->whatsapp_test();
        if (!$this->whatsapp) return;

        $messages = $this->get_message_pending();
        if (empty($messages)) return;
        for ($i = 0; $i < count($messages); $i++) {
            $cliente_id = $messages[$i]['cliente_id'];
            $type_message = $messages[$i]['type_message'];
            $id = $messages[$i]['message_id'];
            $phone = $messages[$i]['phone'];
            $msj = $messages[$i]["message"];
            $data = array("phone" => $phone, "message" => $msj);
            $response = $this->whatsapp($data, $cliente_id, $type_message);
            if ($response != "ok") return;
            if ($response == "ok") {
                $this->delete_message($id);
            }
        }
    }


    public function whatsapp_test()
    {
        $brand = $this->get_brand();
        $phone = $brand[0]["codigo_pais"] . $brand[0]["phone"];
        $message = "Whatsapp connected";
        $whatsapp = [ "phone" => $phone, "message" => $message ];
        return $this->whatsapp(
            $whatsapp,
            "ADMIN",
            "test"
        );
    }


    public function update_status_list() 
    {
        $status = $this->get_all_status();
        for($i = 0; $i < count($status); $i++) {
            $id = $status[$i]['status_id'];
            $SQL = "CALL update_status($id)";
            $query = Flight::gnconn()->prepare($SQL);
            $query->execute();
        }
    }


    public function get_all_status()
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM clientes_status 
            ORDER BY status_id ASC
        ");
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function delete_message($id)
    {
        $SQL = "DELETE FROM message_retry WHERE message_id = $id";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function get_message_pending()
    {
        $SQL = "SELECT * FROM message_retry";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    /**
     * mikrotik_check_connected
     *
     * @return void
     */
    public function mikrotik_check_connected()
    {
        $mikrotiks = $this->get_all_mikrotiks();
        for($i = 0; $i < count($mikrotiks); $i++) {
            $id = $mikrotiks[$i]['mikrotik_id'];
            $conn = new Mikrotik(
                $mikrotiks[$i]['mikrotik_ip'],
                $mikrotiks[$i]['mikrotik_usuario'],
                $mikrotiks[$i]['mikrotik_password'],
                $mikrotiks[$i]['mikrotik_puerto']
            );

            if (!$conn->connected) return;

            if ($conn->connected) {
                $this->retry_mikrotik_add($id);
                $this->retry_mikrotik_remove($id);
                $this->retry_mikrotik_set($id);
            }
        }
    }

    
    /**
     * get_all_mikrotiks
     *
     * @return array
     */
    public function get_all_mikrotiks()
    {
        $SQL = "SELECT * FROM mikrotiks WHERE mikrotik_status = 1";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
    }


    public function retry_mikrotik_add($id)
    {
        $data = $this->search_mikrotik_add($id);
        if (empty($data)) return;
        if (!empty($adds)) {
            foreach($data as $customer) {
                $module = $customer['module'];
                if ($module) {
                    switch($module) {
                        case 'morosos':
                            $this->add_data_from_morosos($customer);
                        break;
                        case 'all':
                            $this->add_data_from_all($customer);
                        break;
                        case 'filter_rules':
                            $this->add_data_from_filter($customer);
                        break;
                        default: // No hacer nada
                    }
                }
            }
        }
    }


    public function add_data_from_morosos($customer)
    {
        $cliente_id = $customer['cliente_id'];
        $module = $customer['module'];
        $method = $customer['metodo_bloqueo'];
        $ip = $customer['mikrotik_ip'];
        $user = $customer['mikrotik_usuario'];
        $pass = $customer['mikrotik_password'];
        $port = $customer['mikrotik_puerto'];
        $cliente_nombres = $customer['cliente_nombres']." ".$customer['cliente_apellidos'];
        $conn = new Mikrotik($ip, $user, $pass, $port);
        if (!$conn->connected) return;

        switch($method) {
            case 'DHCP':
                $conn->add_from_address_list(
                    $cliente_nombres,
                    $customer['cliente_ip'],
                    "MOROSOS"
                );
                $conn->disconnect();
                $this->delete_item(
                    "mikrotik_retry_add", 
                    $cliente_id, 
                    $module
                );
            break;
            case 'ARP':
                $conn->add_from_address_list(
                    $cliente_nombres,
                    $customer['cliente_ip'],
                    "MOROSOS"
                );
                $conn->disconnect();
                $this->delete_item(
                    "mikrotik_retry_add", 
                    $cliente_id, 
                    $module
                );
            break;

            case 'PPPOE':
                $conn->disabled_secret(
                    $customer['user_pppoe'],
                    $customer['profile']
                );
                $conn->disconnect();
                
                $this->delete_item(
                    "mikrotik_retry_add", 
                    $cliente_id, 
                    $module
                );
            break;
            default:
        }
    }


    public function add_data_from_all($customer)
    {
        $cliente_id = $customer['cliente_id'];
        $server = $customer['server'];
        $module = $customer['module'];
        $method = $customer['metodo_bloqueo'];
        $ip = $customer['mikrotik_ip'];
        $user = $customer['mikrotik_usuario'];
        $pass = $customer['mikrotik_password'];
        $port = $customer['mikrotik_puerto'];
        $cliente_nombres = $customer['cliente_nombres']." ".$customer['cliente_apellidos'];

        $conn = new Mikrotik($ip, $user, $pass, $port);
        if (!$conn->connected) return;

        switch($method) {
            case 'DHCP':
                $conn->add_from_address_list(
                    $cliente_nombres,
                    $customer['cliente_ip'],
                    "ACTIVE"
                );

                $conn->add_from_dhcp_leases(
                    $cliente_nombres,
                    $customer['cliente_ip'],
                    $customer['cliente_mac'],
                    $customer['server'],
                );

                $conn->add_from_queue_simple(
                    $cliente_nombres,
                    $customer['cliente_ip'],
                    $customer['ancho_banda'],
                    $customer['parent_queue']
                );

                $conn->remove_from_address_list(
                    $customer['cliente_ip'],
                    "MOROSOS"
                );

                $conn->remove_from_filter_rules(
                    $customer['cliente_mac'],
                );

                $conn->disconnect();

                $this->delete_item(
                    "mikrotik_retry_add", 
                    $cliente_id, 
                    $module
                );
            break;

            case 'ARP':
                $conn->add_from_address_list(
                    $cliente_nombres,
                    $customer['cliente_ip'],
                    "ACTIVE"
                );

                $conn->add_from_arp_list(
                    $customer['cliente_ip'],
                    $customer['cliente_mac'],
                    $cliente_nombres,
                    $customer['interface_arp'],
                );

                $conn->add_from_queue_simple(
                    $cliente_nombres,
                    $customer['cliente_ip'],
                    $customer['ancho_banda'],
                    $customer['parent_queue'],
                );

                $conn->remove_from_address_list(
                    $customer['cliente_ip'],
                    "MOROSOS"
                );

                $conn->remove_from_filter_rules(
                    $customer['cliente_mac'],
                );

                $conn->disconnect();
                
                $this->delete_item(
                    "mikrotik_retry_add", 
                    $cliente_id, 
                    $module
                );
            break;

            case 'PPPOE':
                $conn->add_from_secrets(
                    $customer['user_pppoe'],
                    $customer['password_pppoe'],
                    $customer['profile'],
                    $customer['cliente_ip']
                );

                $conn->disconnect();
                
                $this->delete_item(
                    "mikrotik_retry_add", 
                    $cliente_id, 
                    $module
                );
            break;
            default:
        }
    }


    public function add_data_from_filter($customer)
    {
        $cliente_id = $customer['cliente_id'];
        $module = $customer['module'];
        $method = $customer['metodo_bloqueo'];
        $ip = $customer['mikrotik_ip'];
        $user = $customer['mikrotik_usuario'];
        $pass = $customer['mikrotik_password'];
        $port = $customer['mikrotik_puerto'];
        $cliente_nombres = $customer['cliente_nombres']." ".$customer['cliente_apellidos'];

        $conn = new Mikrotik($ip, $user, $pass, $port);
        if (!$conn->connected) return;

        switch($method) {
            case 'DHCP':
                $conn->add_from_filter_rules(
                    $cliente_nombres,
                    $customer['cliente_mac']
                );

                $conn->disconnect();

                $this->delete_item(
                    "mikrotik_retry_add", 
                    $cliente_id, 
                    $module
                );
            break;
            default:
        }
    }


    public function delete_item($table, $cliente_id, $module)
    {
        $SQL = "DELETE FROM `$table` WHERE cliente_id = '$cliente_id' AND module = '$module'";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
    }


    public function retry_mikrotik_remove($id)
    {
        $data = $this->search_mikrotik_remove($id);
        if (empty($data)) return;
        if (!empty($data)) {
            foreach($data as $customer) {
                $module = $customer['module'];
                if ($module) {
                    switch($module) {
                        case 'morosos':
                            $this->remove_data_from_morosos($customer);
                        break;
                        case 'all':
                            $this->remove_data_from_all($customer);
                        break;
                        case 'filter_rules':
                            $this->remove_data_from_filter($customer);
                        break;
                        default: // No hacer nada
                    }
                }
            }
        }
    }


    public function remove_data_from_morosos($customer)
    {
        $cliente_id = $customer['cliente_id'];
        $module = $customer['module'];
        $method = $customer['metodo_bloqueo'];
        $ip = $customer['mikrotik_ip'];
        $user = $customer['mikrotik_usuario'];
        $pass = $customer['mikrotik_password'];
        $port = $customer['mikrotik_puerto'];

        $conn = new Mikrotik($ip, $user, $pass, $port);
        if (!$conn->connected) return;
        if ($conn->connected) {
            switch($method) {
                case 'DHCP':
                    $conn->remove_from_address_list(
                        $customer['cliente_ip'],
                        "MOROSOS"
                    );

                    $conn->disconnect();

                    $this->delete_item(
                        "mikrotik_retry_remove", 
                        $cliente_id, 
                        $module
                    );
                break;

                case 'ARP':
                    $conn->remove_from_address_list(
                        $customer['cliente_ip'],
                        "MOROSOS"
                    );
                    
                    $conn->disconnect();

                    $this->delete_item(
                        "mikrotik_retry_remove", 
                        $cliente_id, 
                        $module
                    );
                break;

                case 'PPPOE':
                    $conn->enable_secret(
                        $customer['user_pppoe'],
                        $customer['profile']
                    );
                    
                    $conn->disconnect();

                    $this->delete_item(
                        "mikrotik_retry_remove", 
                        $cliente_id, 
                        $module
                    );
                break;
                default:
            }
        }
    }


    public function remove_data_from_all($customer)
    {
        $cliente_id = $customer['cliente_id'];
        $module = $customer['module'];
        $method = $customer['metodo_bloqueo'];
        $ip = $customer['mikrotik_ip'];
        $user = $customer['mikrotik_usuario'];
        $pass = $customer['mikrotik_password'];
        $port = $customer['mikrotik_puerto'];

        $conn = new Mikrotik($ip, $user, $pass, $port);
        if (!$conn->connected) return;

        switch($method) {
            case 'DHCP':
                $conn->remove_from_address_list(
                    $customer['cliente_ip'],
                    "ACTIVE"
                );

                $conn->remove_from_dhcp_leases(
                    $customer['cliente_ip'],
                    $customer['cliente_mac']
                );

                $conn->remove_customer_queue(
                    $customer['cliente_ip']
                );

                $conn->disconnect();

                $this->delete_item(
                    "mikrotik_retry_remove", 
                    $cliente_id, 
                    $module
                );
            break;

            case 'ARP':
                $conn->remove_from_address_list(
                    $customer['cliente_ip'],
                    "ACTIVE"
                );

                $conn->remove_from_arp_list(
                    $customer['cliente_ip'],
                    $customer['cliente_mac']
                );

                $conn->remove_customer_queue(
                    $customer['cliente_ip']
                );

                $conn->disconnect();
                
                $this->delete_item(
                    "mikrotik_retry_remove", 
                    $cliente_id, 
                    $module
                );
            break;

            case 'PPPOE':
                $conn->remove_customer_secrets(
                    $customer['user_pppoe'],
                    $customer['profile']
                );

                $conn->disconnect();
                
                $this->delete_item(
                    "mikrotik_retry_remove", 
                    $cliente_id, 
                    $module
                );
            break;
            default:
        }
    }


    public function remove_data_from_filter($customer)
    {
        $cliente_id = $customer['cliente_id'];
        $module = $customer['module'];
        $method = $customer['metodo_bloqueo'];
        $ip = $customer['mikrotik_ip'];
        $user = $customer['mikrotik_usuario'];
        $pass = $customer['mikrotik_password'];
        $port = $customer['mikrotik_puerto'];
        $conn = new Mikrotik($ip, $user, $pass, $port);
        if (!$conn->connected) return;
        if ($conn->connected) {
            switch($method) {
                case 'DHCP':
                    $conn->remove_from_filter_rules(
                        $customer['cliente_mac']
                    );
                    $conn->disconnect();
                    $this->delete_item(
                        "mikrotik_retry_remove", 
                        $cliente_id, 
                        $module
                    );
                break;
                default:
            }
        }
    }


    public function retry_mikrotik_set($id) 
    {
        $data = $this->search_mikrotik_set($id);
        if (empty($data)) return;
        if (!empty($data)) {
            foreach($data as $customer) {
                $module = $customer['module'];
                if ($module) {
                    switch($module) {
                        case 'all':
                            $this->set_data_from_all($customer);
                        break;
                        default: // No hacer nada
                    }
                }
            }
        }
    }


    public function set_data_from_all($customer)
    {
        $cliente_id = $customer['cliente_id'];
        $nombres = $customer['cliente_nombres']. " " .$customer['cliente_apellidos'];
        $module = $customer['module'];
        $method = $customer['prev_metodo'];
        $ip = $customer['mikrotik_ip'];
        $user = $customer['mikrotik_usuario'];
        $pass = $customer['mikrotik_password'];
        $port = $customer['mikrotik_puerto'];
        $conn = new Mikrotik($ip, $user, $pass, $port);
        if (!$conn->connected) return;
        if ($conn->connected) {
            switch($method) {
                case 'DHCP':
                    $conn->update_from_dhcp_leases(
                        $nombres, 
                        $customer['prev_ip'], 
                        $customer['prev_mac'], 
                        $customer['cliente_ip'], 
                        $customer['cliente_mac'], 
                        $customer['server']
                    );

                    $conn->update_from_queues(
                        $nombres, 
                        $customer['prev_ip'], 
                        $customer['cliente_ip'], 
                        $customer['ancho_banda']
                    );

                    $conn->update_from_address_list(
                        $nombres, 
                        $customer['prev_ip'], 
                        $customer['cliente_ip'],
                        "ACTIVE"
                    );
                    
                    $conn->disconnect();

                    $this->delete_item(
                        "mikrotik_retry_set", 
                        $cliente_id, 
                        $module
                    );
                break;

                case 'ARP':
                    $conn->update_from_arp_list(
                        $nombres, 
                        $customer['prev_ip'], 
                        $customer['prev_mac'], 
                        $customer['cliente_ip'], 
                        $customer['cliente_mac'], 
                        $customer['interface']
                    );

                    $conn->update_from_queues(
                        $nombres, 
                        $customer['prev_ip'], 
                        $customer['cliente_ip'], 
                        $customer['ancho_banda']
                    );

                    $conn->update_from_address_list(
                        $nombres, 
                        $customer['prev_ip'], 
                        $customer['cliente_ip'],
                        "ACTIVE"
                    );
                    
                    $conn->disconnect();

                    $this->delete_item(
                        "mikrotik_retry_set", 
                        $cliente_id, 
                        $module
                    );
                break;

                case 'PPPOE':
                    $conn->update_from_secret(
                        $customer['prev_profile'],
                        $customer['profile'], 
                        $customer['prev_user'], 
                        $customer['user_pppoe'], 
                        $customer['password_pppoe']
                    );

                    $conn->disconnect();

                    $this->delete_item(
                        "mikrotik_retry_set", 
                        $cliente_id, 
                        $module
                    );
                break;
                default:
            }
        }
    }


    public function search_mikrotik_add($id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                mikrotik_retry_add.cliente_id, 
                mikrotik_retry_add.mikrotik_id, 
                mikrotik_retry_add.module, 
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, 
                clientes.cliente_nombres, 
                clientes.cliente_apellidos, 
                clientes.cliente_ip, 
                clientes.cliente_mac, 
                clientes.cliente_email, 
                clientes.metodo_bloqueo, 
                clientes.server, 
                clientes.interface_arp, 
                clientes.profile,
                clientes.user_pppoe,
                clientes.password_pppoe, 
                clientes_servicios.cliente_corte, 
                clientes_servicios.cliente_paquete, 
                paquetes.ancho_banda, 
                mikrotiks.* 
            FROM mikrotik_retry_add 
            INNER JOIN clientes 
            ON mikrotik_retry_add.cliente_id = clientes.cliente_id 
            INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN paquetes 
            ON clientes_servicios.cliente_paquete = paquetes.idpaquete 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            WHERE mikrotiks.mikrotik_id = ? 
            ORDER BY clientes.cliente_id 
            DESC LIMIT 10
        ");
        $query->execute([ $id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function search_mikrotik_remove($id)
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                mikrotik_retry_remove.cliente_id, 
                mikrotik_retry_remove.mikrotik_id, 
                mikrotik_retry_remove.cliente_ip, 
                mikrotik_retry_remove.cliente_mac, 
                mikrotik_retry_remove.list, 
                mikrotik_retry_remove.server,
                mikrotik_retry_remove.interface_arp,
                mikrotik_retry_remove.profile,
                mikrotik_retry_remove.module, 
                clientes.metodo_bloqueo,
                clientes.cliente_email,
                mikrotiks.* 
            FROM mikrotik_retry_remove 
            CROSS JOIN clientes 
            ON mikrotik_retry_remove.cliente_id = clientes.cliente_id 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN paquetes 
            ON clientes_servicios.cliente_paquete = paquetes.idpaquete 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            WHERE mikrotiks.mikrotik_id = ?
            ORDER BY clientes.cliente_id 
            DESC LIMIT 10
        ");
        $query->execute([ $id ]);
        $rows = $query->fetchAll();
        return $rows;
    }


    public function search_mikrotik_set($id) 
    {
        $query = Flight::gnconn()->prepare("
            SELECT 
                mikrotik_retry_set.*,
                clientes.cliente_id,
                clientes.cliente_nombres,
                clientes.cliente_apellidos,
                CONCAT(clientes.cliente_nombres, ' ', clientes.cliente_apellidos) AS nombres, 
                clientes.cliente_ip, 
                clientes.cliente_mac,
                clientes.cliente_email,
                clientes.cliente_telefono,
                clientes.metodo_bloqueo,
                clientes.server,
                clientes.interface_arp,
                clientes.profile,
                clientes.user_pppoe,
                clientes.password_pppoe,
                paquetes.ancho_banda, 
                mikrotiks.* 
            FROM mikrotik_retry_set 
            INNER JOIN clientes 
            ON mikrotik_retry_set.cliente_id = clientes.cliente_id 
            INNER JOIN clientes_servicios 
            ON clientes.cliente_id = clientes_servicios.cliente_id 
            INNER JOIN paquetes 
            ON clientes_servicios.cliente_paquete = paquetes.idpaquete 
            INNER JOIN colonias 
            ON clientes_servicios.colonia = colonias.colonia_id 
            INNER JOIN mikrotiks 
            ON colonias.mikrotik_control = mikrotiks.mikrotik_id 
            WHERE mikrotiks.mikrotik_id = ?
            ORDER BY clientes.cliente_id DESC LIMIT 10
        ");
        $query->execute([ $id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

}