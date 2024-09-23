<?php
require 'controller/database/customers/Read.php';
require 'controller/database/customers/Customers.php';

Flight::route(
    'POST /@usuario_id/customers', 
    function ($usuario_id) {
        require 'controller/database/customers/create.php';
    }
);

// Update customer by id request formdata
Flight::route(
    "POST /@usuario_id/customers/update", 
    function ($usuario_id) {
        require 'controller/database/customers/update.php';
    }
);

// List by customer filters
Flight::route(
    'GET /@usuario_id/customers', 
    function ($usuario_id) {
        require 'controller/database/customers/list.php';
    }
);

// Buscar un cliente por si id 
Flight::route(
    'GET /@usuario_id/customers/details/@cliente_id', 
    function ($usuario_id, $cliente_id) {
        require 'controller/database/customers/details.php';
    }
);

Flight::route(
    "GET /@usuario_id/customers/ping/@cliente_id", 
    function ($usuario_id, $cliente_id) {
        require 'controller/database/customers/ping.php';
    }
);

Flight::route(
    "GET /@usuario_id/customers/layoff/@cliente_id", 
    function ($usuario_id, $cliente_id) {
        require "controller/database/customers/layoff.php";
    }
);

Flight::route(
    "DELETE /@usuario_id/customers/unsubscribe/@cliente_id", 
    function ($usuario_id, $cliente_id) {
        require 'controller/database/customers/unsubscribe.php';
    }
);


Flight::route('POST /@usuario_id/customers/consunption/', 
    function ($usuario_id) {
        require 'controller/database/customers/consunption.php';
    }
);

Flight::route(
    "GET /@usuario_id/customers/history/@cliente_id", 
    function ($usuario_id, $cliente_id) {
        require 'controller/database/customers/history.php';
    }
);

// busca un email ingresado
Flight::route('GET /@usuario_id/customers/email/@cliente_email', 
    function ($usuario_id, $cliente_email) {
        require 'controller/database/customers/search-email.php';
    }
);

// busca un telefono ingresado
Flight::route('GET /@usuario_id/customers/telefono/@cliente_telefono', 
    function ($usuario_id, $cliente_telefono) {
        require 'controller/database/customers/search-phone.php';
    }
);

// Busca una mac ingresada //
Flight::route('GET /@usuario_id/customers/mac/@cliente_mac', 
    function ($usuario_id, $cliente_mac) {
        require 'controller/database/customers/search-mac.php';
    }
);

Flight::route('GET /@usuario_id/customers/ip/@cliente_ip', 
    function ($usuario_id, $cliente_ip) {
        require 'controller/database/customers/search-ipv4.php';
    }
);
 
Flight::route('GET /@usuario_id/customers/serie/@serie_modem', 
    function ($usuario_id, $serie_modem) {
        require 'controller/database/customers/search-serie.php';
    }
);


Flight::route('POST /@usuario_id/customers/ine', 
    function ($usuario_id) {
        require 'controller/database/customers/change-ine.php';
    }
);

Flight::route("GET /@usuario_id/customers/enable/@cliente_id", function ($usuario_id, $cliente_id) {
    require 'controller/database/customers/enable-customer.php';
});



Flight::route('POST /@usuario_id/customers/layoff/lots', function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    }
    if ($is_user) {
        $request = Flight::request()->getBody();
        $customer = new Customer();
        $suspencion = $customer->suspended_lots($request);
        if ($suspencion) {
            Flight::json(
                array(
                    "status" => 200,
                    "title" => "Proceso terminado!",
                    "details" => "Los clientes fueron suspendidos correctamente!",
                    "customer" => $customer->select_customers_lots($request),
                    "data" => $request,
                )
            );
        }
        if (!$suspencion) {
            Flight::json(
                array(
                    "status" => 500,
                    "title" => "Ocurrió un error!",
                    "details" => "Los clientes no fueron suspendidos!",
                    "data" => $request
                )
            );
        }
    }
});


Flight::route('GET /@usuario_id/customers/colonia/@colonia', function ($usuario_id, $colonia) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    } else {
        if (!isset($colonia) || !is_numeric($colonia))
            $colonia = 1;
        $customers = new ReadCustomers(array("colonia" => $colonia));
        Flight::json(
            array(
                "status" => 200,
                "title" => "Encontrado!",
                "details" => "Más de una fila fue encontrada",
                "data" => $customers
            )
        );
    }
});

Flight::route('GET /@usuario_id/customers/corte/@cliente_corte', function ($usuario_id, $cliente_corte) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    } else {
        $customers = new ReadCustomers(array("cliente_corte" => $cliente_corte));
        Flight::json(
            array(
                "status" => 200,
                "title" => "Encontrado!",
                "details" => "Más de una fila fue encontrada",
                "data" => $customers
            )
        );
    }
});

Flight::route('GET /@usuario_id/customers/search/@params', function ($usuario_id, $params) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    } else {
        $customers = new ReadCustomers(array("search" => $params));
        Flight::json(
            array(
                "status" => 200,
                "title" => "Encontrado!",
                "details" => "Más de una fila fue encontrada",
                "data" => $customers
            )
        );
    }
});



Flight::route('GET /@usuario_id/customers/ap', function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    } else {
        $SQL = "SELECT * FROM `addresses` AS ap ORDER BY `addresses`.`name` ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        if (empty($rows)) {
            Flight::json(
                array(
                    "status" => 204,
                    "title" => "No encontrado!",
                    "details" => "No se encontraron resultados!",
                    "data" => $rows
                )
            );
        }
        if (!empty($rows)) {
            Flight::json(
                array(
                    "status" => 200,
                    "title" => "Encontrado!",
                    "details" => "Más de una fila fue encontrada",
                    "data" => $rows
                )
            );
        }
    }
});

// Agregar una nueva ap
Flight::route('POST /@usuario_id/addresses', function ($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    } else {
        $request = Flight::request()->data;
        $repeat_address = ReadCustomers::search_address_IPv4($request);
        $repeat_name = ReadCustomers::search_name_address($request);
        if (!$repeat_address || !$repeat_name) {
            Flight::json(
                array(
                    "status" => 409,
                    "title" => "Conflicto!",
                    "details" => "La ap ya existe!",
                    "data" => []
                )
            );
        }
        if ($repeat_name && $repeat_address) {
            $add_ap = Customer::add_ap_ping($request);
            if (!$add_ap) {
                Flight::json(
                    array(
                        "status" => 500,
                        "title" => "Error interno!",
                        "details" => "No se agrego la informacion",
                        "data" => []
                    )
                );
            }
            if ($add_ap) {
                Flight::json([
                    "status" => 200,
                    "title" => "Creado!",
                    "details" => "Antena registrada correctamente!",
                    "data" => $request
                ]);
            }
        }
    }
});

Flight::route('GET /@usuario_id/customers/resources/payment/@cliente_id/@limit', function ($usuario_id, $cliente_id, $limit) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado!");
    } else {
        $customer = new ReadCustomers();
        $customer->get_resources_customer_payment($cliente_id, $limit);
        Flight::json(
            array(
                "status" => 200,
                "title" => "Encontrado!",
                "details" => "Más de una fila fue encontrada",
                "data" => $customer
            )
        );
    }
});

Flight::route('GET /@usuario_id/customers/checkout/@cliente_id', function ($usuario_id, $cliente_id) {
    require 'controller/database/customers/checkout-mikrotik.php';
});




Flight::route("GET /@usuario_id/customers/types", function($usuario_id) {
    $login = new Login();
    $is_user = $login->is_user($usuario_id);
    if (!$is_user) {
        Flight::halt(401, "No autorizado");
    }

    if ($is_user) {
        $customer = new ReadCustomers();
        $customer = $customer->get_all_type_customer();
        Flight::json($customer);
    }
});

