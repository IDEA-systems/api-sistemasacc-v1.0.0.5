<?php
/*****************************
 *
 * RouterOS PHP API class v1.6
 * Author: Denis Basta
 * Contributors:
 *    Nick Barnes
 *    Ben Menking (ben [at] infotechsc [dot] com)
 *    Jeremy Jefferson (http://jeremyj.com)
 *    Cristian Deluxe (djcristiandeluxe [at] gmail [dot] com)
 *    Mikhail Moskalev (mmv.rus [at] gmail [dot] com)
 *
 * http://www.mikrotik.com
 * http://wiki.mikrotik.com/wiki/sacc/api_PHP_class
 *
 ******************************/

//class RouterosAPI
class Mikrotik
{
    private $mktIP;
    private $mktUSR;
    private $mktPW;
    private $port; //  Port to connect to (default 8729 for ssl), default local 8728


    public function __construct($mktIP, $mktUSR, $mktPW, $puerto = 8728) {
      $this->mktIP = $mktIP;
      $this->mktUSR = $mktUSR;
      $this->mktPW = $mktPW;
      $this->port = $puerto;
      $this->connect();
    }
    var $debug     = false;  //  Show debug information
    var $connected = false; //  Connection state
    var $ssl = false; //  Connect using SSL (must enable api-ssl in IP/Services)
    var $timeout  = 1;     //  Connection attempt timeout and data read timeout
    var $attempts  = 2;     //  Connection attempt count
    var $delay     = 1;     //  Delay between connection attempts in seconds
    var $socket;            //  Variable for storing socket resource
    var $error_no;          //  Variable for storing connection error number, if any
    var $error_str;         //  Variable for storing connection error text, if any

    /* Check, can be var used in foreach  */
    public function isIterable($var)
    {
        return $var !== null
                && (is_array($var)
                || $var instanceof Traversable
                || $var instanceof Iterator
                || $var instanceof IteratorAggregate
                );
    }

    /**
     * Print text for debug purposes
     *
     * @param string      $text       Text to print
     *
     * @return void
     */
    public function debug($text)
    {
        if ($this->debug) {
            echo $text . "\n";
        }
    }


    /**
     *
     *
     * @param string        $length
     *
     * @return mixed
     */
    public function encodeLength($length)
    {
        if ($length < 0x80) {
            $length = chr($length);
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            $length = chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            $length = chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x10000000) {
            $length |= 0xE0000000;
            $length = chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length >= 0x10000000) {
            $length = chr(0xF0) . chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        }

        return $length;
    }


    /**
     * Login to RouterOS
     *
     * @param string      $ip         Hostname (IP or domain) of the RouterOS server
     * @param string      $login      The RouterOS username
     * @param string      $password   The RouterOS password
     *
     * @return boolean                If we are connected or not
     */
    public function connect() {

      $ip = $this->mktIP;
      $login = $this->mktUSR;
      $password = $this->mktPW;

        for ($ATTEMPT = 1; $ATTEMPT <= $this->attempts; $ATTEMPT++) {
            $this->connected = false;
            $PROTOCOL = ($this->ssl ? 'ssl://' : '' );
            $context = stream_context_create(array('ssl' => array('ciphers' => 'ADH:ALL', 'verify_peer' => false, 'verify_peer_name' => false)));
            $this->debug('Connection attempt #' . $ATTEMPT . ' to ' . $PROTOCOL . $ip . ':' . $this->port . '...');
            $this->socket = @stream_socket_client($PROTOCOL . $ip.':'. $this->port, $this->error_no, $this->error_str, $this->timeout, STREAM_CLIENT_CONNECT,$context);
            if ($this->socket) {
                socket_set_timeout($this->socket, $this->timeout);
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);
                $RESPONSE = $this->read(false);
                if (isset($RESPONSE[0])) {
                    if ($RESPONSE[0] == '!done') {
                        if (!isset($RESPONSE[1])) {
                            // Login method post-v6.43
                            $this->connected = true;
                            break;
                        } else {
                            // Login method pre-v6.43
                            $MATCHES = array();
                            if (preg_match_all('/[^=]+/i', $RESPONSE[1], $MATCHES)) {
                                if ($MATCHES[0][0] == 'ret' && strlen($MATCHES[0][1]) == 32) {
                                    $this->write('/login', false);
                                    $this->write('=name=' . $login, false);
                                    $this->write('=response=00' . md5(chr(0) . $password . pack('H*', $MATCHES[0][1])));
                                    $RESPONSE = $this->read(false);
                                    if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done') {
                                        $this->connected = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                fclose($this->socket);
            }
            sleep($this->delay);
        }

        if ($this->connected) {
            $this->debug('Connected...');
        } else {
            $this->debug('Error...');
        }
        return $this->connected;
    }


    /**
     * Disconnect from RouterOS
     *
     * @return void
     */
    public function disconnect()
    {
        // let's make sure this socket is still valid.  it may have been closed by something else
        if( is_resource($this->socket) ) {
            fclose($this->socket);
        }
        $this->connected = false;
        $this->debug('Disconnected...');
    }


    /**
     * Parse response from Router OS
     *
     * @param array       $response   Response data
     *
     * @return array                  Array with parsed data
     */
    public function parseResponse($response)
    {
        if (is_array($response)) {
            $PARSED      = array();
            $CURRENT     = null;
            $singlevalue = null;
            foreach ($response as $x) {
                if (in_array($x, array('!fatal','!re','!trap'))) {
                    if ($x == '!re') {
                        $CURRENT =& $PARSED[];
                    } else {
                        $CURRENT =& $PARSED[$x][];
                    }
                } elseif ($x != '!done') {
                    $MATCHES = array();
                    if (preg_match_all('/[^=]+/i', $x, $MATCHES)) {
                        if ($MATCHES[0][0] == 'ret') {
                            $singlevalue = $MATCHES[0][1];
                        }
                        $CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
                    }
                }
            }

            if (empty($PARSED) && !is_null($singlevalue)) {
                $PARSED = $singlevalue;
            }

            return $PARSED;
        } else {
            return array();
        }
    }


    /**
     * Parse response from Router OS
     *
     * @param array       $response   Response data
     *
     * @return array                  Array with parsed data
     */
    public function parseResponse4Smarty($response)
    {
        if (is_array($response)) {
            $PARSED      = array();
            $CURRENT     = null;
            $singlevalue = null;
            foreach ($response as $x) {
                if (in_array($x, array('!fatal','!re','!trap'))) {
                    if ($x == '!re') {
                        $CURRENT =& $PARSED[];
                    } else {
                        $CURRENT =& $PARSED[$x][];
                    }
                } elseif ($x != '!done') {
                    $MATCHES = array();
                    if (preg_match_all('/[^=]+/i', $x, $MATCHES)) {
                        if ($MATCHES[0][0] == 'ret') {
                            $singlevalue = $MATCHES[0][1];
                        }
                        $CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
                    }
                }
            }
            foreach ($PARSED as $key => $value) {
                $PARSED[$key] = $this->arrayChangeKeyName($value);
            }
            return $PARSED;
            if (empty($PARSED) && !is_null($singlevalue)) {
                $PARSED = $singlevalue;
            }
        } else {
            return array();
        }
    }


    /**
     * Change "-" and "/" from array key to "_"
     *
     * @param array       $array      Input array
     *
     * @return array                  Array with changed key names
     */
    public function arrayChangeKeyName(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $tmp = str_replace("-", "_", $k);
                $tmp = str_replace("/", "_", $tmp);
                if ($tmp) {
                    $array_new[$tmp] = $v;
                } else {
                    $array_new[$k] = $v;
                }
            }
            return $array_new;
        } else {
            return $array;
        }
    }


    /**
     * Read data from Router OS
     *
     * @param boolean     $parse      Parse the data? default: true
     *
     * @return array                  Array with parsed or unparsed data
     */
    public function read($parse = true)
    {
        $RESPONSE     = array();
        $receiveddone = false;
        while (true) {
            // Read the first byte of input which gives us some or all of the length
            // of the remaining reply.
            $BYTE   = ord(fread($this->socket, 1));
            $LENGTH = 0;
            // If the first bit is set then we need to remove the first four bits, shift left 8
            // and then read another byte in.
            // We repeat this for the second and third bits.
            // If the fourth bit is set, we need to remove anything left in the first byte
            // and then read in yet another byte.
            if ($BYTE & 128) {
                if (($BYTE & 192) == 128) {
                    $LENGTH = (($BYTE & 63) << 8) + ord(fread($this->socket, 1));
                } else {
                    if (($BYTE & 224) == 192) {
                        $LENGTH = (($BYTE & 31) << 8) + ord(fread($this->socket, 1));
                        $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                    } else {
                        if (($BYTE & 240) == 224) {
                            $LENGTH = (($BYTE & 15) << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        } else {
                            $LENGTH = ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        }
                    }
                }
            } else {
                $LENGTH = $BYTE;
            }

            $_ = "";

            // If we have got more characters to read, read them in.
            if ($LENGTH > 0) {
                $_      = "";
                $retlen = 0;
                while ($retlen < $LENGTH) {
                    $toread = $LENGTH - $retlen;
                    $_ .= fread($this->socket, $toread);
                    $retlen = strlen($_);
                }
                $RESPONSE[] = $_;
                $this->debug('>>> [' . $retlen . '/' . $LENGTH . '] bytes read.');
            }

            // If we get a !done, make a note of it.
            if ($_ == "!done") {
                $receiveddone = true;
            }

            $STATUS = socket_get_status($this->socket);
            if ($LENGTH > 0) {
                $this->debug('>>> [' . $LENGTH . ', ' . $STATUS['unread_bytes'] . ']' . $_);
            }

            if ((!$this->connected && !$STATUS['unread_bytes']) || ($this->connected && !$STATUS['unread_bytes'] && $receiveddone)) {
                break;
            }
        }

        if ($parse) {
            $RESPONSE = $this->parseResponse($RESPONSE);
        }

        return $RESPONSE;
    }


    /**
     * Write (send) data to Router OS
     *
     * @param string      $command    A string with the command to send
     * @param mixed       $param2     If we set an integer, the command will send this data as a "tag"
     *                                If we set it to boolean true, the funcion will send the comand and finish
     *                                If we set it to boolean false, the funcion will send the comand and wait for next command
     *                                Default: true
     *
     * @return boolean                Return false if no command especified
     */
    public function write($command, $param2 = true)
    {
        if ($command) {
            $data = explode("\n", $command);
            foreach ($data as $com) {
                $com = trim($com);
                fwrite($this->socket, $this->encodeLength(strlen($com)) . $com);
                $this->debug('<<< [' . strlen($com) . '] ' . $com);
            }

            if (gettype($param2) == 'integer') {
                fwrite($this->socket, $this->encodeLength(strlen('.tag=' . $param2)) . '.tag=' . $param2 . chr(0));
                $this->debug('<<< [' . strlen('.tag=' . $param2) . '] .tag=' . $param2);
            } elseif (gettype($param2) == 'boolean') {
                fwrite($this->socket, ($param2 ? chr(0) : ''));
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * IPv4_generator                   Generador de IPv4
     *
     * @param  number $segment          Segmento de inicio
     * @param  number $range            Rango de inicio tiene que ser uno
     * @param  number $hosts            Numero de hosts que se quieren crear
     * @param  number $ips              Array vacio donde se guardaran las ips generadas
     * @return array
     */
    public function IPv4_generator($segment = 22, $range = 1, $hosts = 253, $init = "192.168", $ips = []) {
        for ($i = 1; $i < $hosts; $i++) {
        $range++;                              // Aumentar el rango
        if ($range > $hosts) {                 // Si el rango es superior al numero de host
            $segment++;                          // Aumentar el segmento en 1 o sumar 1 al segmento
            $range = 1;                          // Resetear el rango de nuevo a 1
        }
        $newIP = "$init.$segment.$range";      // Generando la IPv4
        array_push($ips, $newIP);              // agregar la ip al array
        }
        return $ips;      // array creado
    }

    /**
     * Write (send) data to Router OS
     *
     * @param string      $com        A string with the command to send
     * @param array       $arr        An array with arguments or queries
     *
     * @return array                  Array with parsed
     */
    public function comm($com, $arr = array())
    {
        $count = count($arr);
        $this->write($com, !$arr);
        $i = 0;
        if ($this->isIterable($arr)) {
            foreach ($arr as $k => $v) {
                switch ($k[0]) {
                    case "?":
                        $el = "$k=$v";
                        break;
                    case "~":
                        $el = "$k~$v";
                        break;
                    default:
                        $el = "=$k=$v";
                        break;
                }

                $last = ($i++ == $count - 1);
                $this->write($el, $last);
            }
        }

        return $this->read();
    }

        
    /**
     * add_from_address_list
     *
     * @param  mixed $comment
     * @param  mixed $address
     * @param  mixed $address_list
     * @return mixed
     */
    public function add_from_address_list($comment, $address, $address_list = "ACTIVE") 
    {
        $list = $this->comm("/ip/firewall/address-list/getall");

        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["address"] == $address && $list[$i]["list"] == $address_list) {
                    $this->comm("/ip/firewall/address-list/remove", [ ".id" => $i ]);
                    break;
                }
            }
        }

        return $this->comm("/ip/firewall/address-list/add", [
            "address" => $address,
            "comment" => $comment, 
            "list" => $address_list
        ]);
    }

        
    /**
     * add_from_dhcp_leases
     *
     * @param  mixed $comment
     * @param  mixed $address
     * @param  mixed $mac_address
     * @param  mixed $server
     * @return mixed
     */
    public function add_from_dhcp_leases($comment, $address, $mac_address, $server = "all") 
    {
        $list = $this->comm("/ip/dhcp-server/lease/getall");
        
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["address"] == $address) {
                    $this->comm("/ip/dhcp-server/lease/remove", [ ".id" => $i ]);
                    break;
                }
            }
        }

        return $this->comm("/ip/dhcp-server/lease/add", [
            "address" => $address,
            "mac-address" => $mac_address,
            "comment" => $comment,
            "server" => $server
        ]);
    }

    
    /**
     * add_from_arp_list
     *
     * @param  mixed $address
     * @param  mixed $mac_address
     * @param  mixed $comment
     * @param  mixed $interface
     * @return mixed
     */
    public function add_from_arp_list($address, $mac_address, $comment,  $interface = "ether1")
    {
        $list = $this->comm("/ip/arp/getall");

        for ($i = 0; $i < count($list); $i++) {
            $mac = isset($list[$i]["mac-address"]) ? $list[$i]["mac-address"] : "";
            $ip = isset($list[$i]["address"]) ? $list[$i]["address"] : "";
            if ($mac == $mac_address || $ip == $address) {
                $this->comm("/ip/arp/remove", [ ".id" => $i ]);
                break;
            }
        }

        // Agregar a la lista arp
        return $this->comm("/ip/arp/add", array(
            "address" => $address,
            "mac-address" => $mac_address,
            "comment" => $comment,
            "interface" => $interface
        ));
    }
    
    /**
     * add_from_queue_simple
     *
     * @param  array $rows
     * @return mixed
     */
    public function add_from_queue_simple($name, $target, $ancho_banda, $parent = "none") 
    {
        $list = $this->comm("/queue/simple/getall");

        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                $target_mkt = isset($list[$i]["target"]) ? explode("/", $list[$i]["target"])[0] : "";
                if ($target_mkt == $target) {
                    $this->comm("/queue/simple/remove", [ ".id" => $i ]);
                    break;
                }
            }
        }

        return $this->comm("/queue/simple/add", [
            "max-limit" => "$ancho_banda",
            "target" => $target,
            "name" => $name,
            "parent" => $parent
        ]);
    }


        
    /**
     * add_from_secrets
     *
     * @param  mixed $name
     * @param  mixed $password
     * @param  mixed $profile
     * @param  mixed $remote_address
     * @param  mixed $disabled
     * @return mixed
     */
    public function add_from_secrets($name, $password, $profile = "default", $remote_address = "", $disabled = "false") 
    {
        $list = $this->comm("/ppp/secret/getall");
        
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["name"] == $name && $list[$i]["profile"] == $profile) {
                    $this->comm("/ppp/secret/remove", [ ".id" => $i ]);
                    break;
                }
            }
        }

        if (empty($remote_address)) {
            return $this->comm("/ppp/secret/add", [
                "name" => $name,
                "password" => $password,
                "profile" => $profile,
                "service" => "pppoe",
                "disabled" => $disabled
            ]);
        }

        if (!empty($remote_address)) {
            return $this->comm("/ppp/secret/add", [
                "name" => $name,
                "password" => $password,
                "profile" => $profile,
                "service" => "pppoe",
                "remote-address" => $remote_address,
                "disabled" => $disabled
            ]);
        }
    }
    
        
    /**
     * add_from_filter_rules
     *
     * @param  mixed $comment
     * @param  mixed $mac_address
     * @return mixed
     */
    public function add_from_filter_rules($comment, $mac_address) {
        
        $list = $this->comm("/ip/firewall/filter/getall");
        
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["src-mac-address"] == $mac_address) {
                    return "<*_*>";
                }
            }
        }
        
        return $this->comm("/ip/firewall/filter/add", [
            "src-mac-address" => $mac_address,
            "comment" => $comment,
            "chain" => "forward",
            "action" => "drop"
        ]);
    }

    
    /**
     * update_from_address_list
     *
     * @param  mixed $comment
     * @param  mixed $prev_address
     * @param  mixed $new_address
     * @param  mixed $list
     * @return mixed
     */
    public function update_from_address_list($comment, $prev_address, $new_address, $address_list = "ACTIVE") 
    {
        if (!$this->connected) return false;
        if ($this->connected) {
            $list = $this->comm("/ip/firewall/address-list/getall");
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["address"] != $prev_address) continue;
                if ($list[$i]["address"] == $prev_address && $list[$i]["list"] == "ACTIVE") {
                    $this->comm("/ip/firewall/address-list/remove", [
                        ".id" => $i
                    ]);

                    return $this->comm("/ip/firewall/address-list/add", [
                        "list" => "ACTIVE",
                        "address" => $new_address,
                        "comment" => $comment
                    ]);
                }
            }

            return $this->comm("/ip/firewall/address-list/add", [
                "list" => "ACTIVE",
                "address" => $new_address,
                "comment" => $comment
            ]);
        }
    }


    public function update_from_queues($name, $prev_target, $new_target, $max_limit, $parent = "none") 
    {
        if (!$this->connected) return false;
        if ($this->connected) {
            $queues = $this->comm("/queue/simple/getall");
            for ($i = 0; $i < count($queues); $i++) {
                $target = explode("/", $queues[$i]["target"])[0];
                if (empty($target)) continue;
                if ($target == $prev_target) {
                    $this->comm("/queue/simple/remove", [
                        ".id" => $i
                    ]);

                    return $this->comm("/queue/simple/add", [
                        "target" => $new_target,
                        "max-limit" => $max_limit,
                        "name" => $name,
                        "parent" => $parent
                    ]);
                }
            }

            return $this->comm("/queue/simple/add", [
                "target" => $new_target,
                "max-limit" => $max_limit,
                "name" => $name,
                "parent" => $parent
            ]);
        }
    }

    public function update_from_dhcp_leases($comment, $prev_address, $prev_mac, $new_address, $new_mac_address, $server) 
    {
        if (!$this->connected) return false;
        if ($this->connected) {
            $leases = $this->comm("/ip/dhcp-server/lease/getall");
            for ($i = 0; $i < count($leases); $i++) {
                if ($leases[$i]["address"] == $prev_address) {
                    $this->comm("/ip/dhcp-server/lease/remove", [
                        ".id" => $i
                    ]);
                }
            }

            return $this->comm("/ip/dhcp-server/lease/add", [
                "address" => $new_address,
                "mac-address" => $new_mac_address,
                "comment" => $comment,
                "server" => $server
            ]);
        }
    }

    public function update_from_arp_list($comment, $prev_address, $prev_mac, $new_address, $new_mac_address, $interface) 
    {
        if (!$this->connected) return false;
        if ($this->connected) {
            $leases = $this->comm("/ip/arp/getall");
            for ($i = 0; $i < count($leases); $i++) {
                if ($leases[$i]["address"] == $prev_address || $leases[$i]["mac-address"] == $prev_mac) {
                    return $this->comm("/ip/arp/set", [
                        ".id" => $i,
                        "address" => $new_address,
                        "mac-address" => $new_mac_address,
                        "comment" => $comment,
                        "interface" => $interface
                    ]);
                }
            }

            return $this->comm("/ip/arp/add", [
                "address" => $new_address,
                "mac-address" => $new_mac_address,
                "comment" => $comment,
                "interface" => $interface
            ]);
        }
    }


    public function update_from_secret($prev_profile, $new_profile, $previus_name, $new_name, $password, $remote = "", $disabled) 
    {
        if (!$this->connected) return false;
        if ($this->connected) {
            $secrets = $this->comm("/ppp/secret/getall");
            for ($i = 0; $i < count($secrets); $i++) {
                $secret_name = $secrets[$i]["name"];
                $profile_secret = $secrets[$i]["profile"];

                if ($secret_name != $previus_name && $profile_secret != $prev_profile) {
                    continue;
                }

                if ($secret_name == $previus_name && $profile_secret == $prev_profile) {
                    return $this->comm("/ppp/secret/set", [
                        ".id" => $i,
                        "name" => $new_name,
                        "password" => $password,
                        "profile" => $new_profile,
                        "service" => "pppoe",
                        "remote-address" => $remote,
                        "disabled" => $disabled
                    ]);
                }
            }

            return $this->comm("/ppp/secret/add", [
                "name" => $new_name,
                "password" => $password,
                "profile" => $new_profile,
                "service" => "pppoe",
                "remote-address" => $remote,
                "disabled" => $disabled
            ]);
        }
    }
    
    /**
     * remove_from_filter_rules
     *
     * @param  array $rows
     * @return mixed
     */
    public function remove_from_filter_rules($mac_address) {
        $list = $this->comm("/ip/firewall/filter/getall", []);
        if (!empty($list)) {
            for ($i = 0; $i < count($list); $i++) {
                if (!isset($list[$i]["src-mac-address"])) continue;
                if (isset($list[$i]["src-mac-address"])) {
                    $src_mac_address = $list[$i]["src-mac-address"];
                    if ($mac_address != $src_mac_address) continue;
                    if ($mac_address == $src_mac_address) {
                        return $this->comm("/ip/firewall/filter/remove", [
                            ".id" => $i
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * remove_customer_queue
     *
     * @param  array $rows
     * @return mixed
     */
    public function remove_customer_queue($target) {
        if (!$this->connected) return;
        if ($this->connected) {
            $queues = $this->comm("/queue/simple/getall");
            for ($i = 0; $i < count($queues); $i++) {
                $current_target = explode("/", $queues[$i]["target"])[0];
                if (empty($current_target)) continue;
                if ($current_target == $target) {
                    return $this->comm("/queue/simple/remove", [
                        ".id" => $i
                    ]);
                }
            }
        }
    }
    
    /**
     * remove_from_address_list
     *
     * @param  array $rows
     * @param  string $list
     * @return mixed
     */
    public function remove_from_address_list($address, $list = "MOROSOS") {
        if (!$this->connected) return false;
        if ($this->connected) {
            $address_list = $this->comm("/ip/firewall/address-list/getall");
            for ($i = 0; $i < count($address_list); $i++) {
                if ($address_list[$i]["address"] != $address) continue;
                if ($address_list[$i]["address"] == $address && $address_list[$i]["list"] == $list) {
                    return $this->comm("/ip/firewall/address-list/remove", [".id" => $i]);
                }
            }
        }
    }


    public function in_address_list($address, $address_list = "MOROSOS") {
        if (!$this->connected) return false;
        if ($this->connected) {
            $found_address = false;
            $list = $this->comm("/ip/firewall/address-list/getall");
            foreach ($list as $item) {
                if ($item['list'] != $address_list) continue;
                if ($item["address"] == $address && $item["list"] == $address_list) {
                    $found_address = true;
                }
            }
            return $found_address;
        }
    }


    public function remove_customer_secrets($name, $profile) {
        if (!$this->connected) return false;
        if ($this->connected) {
            $list = $this->comm("/ppp/secret/getall");
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["name"] != $name) continue;

                if (
                    $list[$i]["name"] == $name &&
                    $list[$i]["profile"] == $profile
                ) {
                    return $this->comm("/ppp/secret/remove", [
                        ".id" => $i
                    ]);
                }
            }
        }
    }

    public function remove_customer_actives($name, $profile) {
        if (!$this->connected) return false;
        if ($this->connected) {
            $list = $this->comm("/ppp/active/getall");
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["name"] != $name) continue;
                if ($list[$i]["name"] == $name) {
                    return $this->comm("/ppp/active/remove", [
                        ".id" => $i
                    ]);
                }
            }
        }
    }

    public function remove_from_arp_list($address, $mac_address) {
        if (!$this->connected) return false;
        if ($this->connected) {
            $list = $this->comm("/ip/arp/getall");
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["address"] != $address) continue;
                if (
                    $list[$i]["address"] == $address &&
                    $list[$i]["mac-address"] == $mac_address
                ) {
                    return $this->comm("/ip/arp/remove", [
                        ".id" => $i
                    ]);
                }
            }
        }
    }
    
    /**
     * remove_from_dhcp_leases
     *
     * @param  mixed $rows
     * @return void
     */
    public function remove_from_dhcp_leases($address, $mac_address) {
        if (!$this->connected) return;
        if ($this->connected) {
            $leases = $this->comm("/ip/dhcp-server/lease/getall");
            for ($i = 0; $i < count($leases); $i++) {
                if ($leases[$i]["address"] != $address) continue;
                if ($leases[$i]["address"] == $address) {
                    $this->comm("/ip/dhcp-server/lease/remove", [
                        ".id" => $i
                    ]);
                }
            }
        }
    }

    
    /**
     * disabled_secret
     *
     * @param  mixed $name
     * @param  mixed $profile
     * @return mixed
     */
    public function disabled_secret($name, $profile) 
    {
        if (!$this->connected) return;
        if ($this->connected) {
            $secrets = $this->comm("/ppp/secret/getall");
            for ($i = 0; $i < count($secrets); $i++) {
                if ($secrets[$i]["name"] != $name) continue;
                if ($secrets[$i]["name"] == $name && $secrets[$i]["profile"] == $profile) {
                    return $this->comm("/ppp/secret/set", [
                        ".id" => $i,
                        "disabled" => "true"
                    ]);
                }
            }
            return [];
        }
    }
    
    /**
     * disabled_secret
     *
     * @param  mixed $name
     * @param  mixed $profile
     * @return mixed
     */
    public function enable_secret($name, $profile) 
    {
        if (!$this->connected) return;
        if ($this->connected) {
            $secrets = $this->comm("/ppp/secret/getall");
            for ($i = 0; $i < count($secrets); $i++) {
                if ($secrets[$i]["name"] != $name) continue;
                if ($secrets[$i]["name"] == $name && $secrets[$i]["profile"] == $profile) {
                    return $this->comm("/ppp/secret/set", [
                        ".id" => $i,
                        "disabled" => "false"
                    ]);
                }
            }
            return [];
        }
    }
    
    
    /**
     * get_consunption_address          Obtener el consumo en simple queues de una ipv4
     *
     * @param  string $ip               IPv4 al que se hara la peticion
     * @return mixed                    Devuelve false si falla o un array con la informacion si acierta
     */
    public function get_consunption_address($ip) {
        if (!$this->connect()) return false;
        $queues = $this->comm("/queue/simple/getall");
        if (!empty($queues)) {
            for($i = 0; $i < count($queues); $i++) {
                if (!str_contains($queues[$i]["target"], '/')) continue;
                $target = explode("/", $queues[$i]["target"])[0];
                if ($target == $ip) {
                    return $queues[$i];
                }
            }
        }
        return false;
    }

    /**
     * Standard destructor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
