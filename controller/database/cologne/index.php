<?php

# Files cologne
require 'controller/database/cologne/Cologne.php';
require 'controller/database/cologne/Read.php';

// Agregar una nueva colonia
Flight::route("POST /@usuario_id/colognes", 
    function ($usuario_id) {
        require 'controller/database/cologne/create.php';
    }
);


// Actualizar una colonia
Flight::route("POST /@usuario_id/colognes/update", 
    function ($usuario_id) {
        require 'controller/database/cologne/update.php';
    }
);


// Deshabilitar una colonia //
Flight::route("DELETE /@usuario_id/colognes/@colonia_id", 
    function ($usuario_id, $colonia_id) {
        require 'controller/database/cologne/delete.php';
    }
);


// Habilitar una colonia 
Flight::route("GET /@usuario_id/colognes/@colonia_id", 
    function ($usuario_id, $colonia_id) {
        require 'controller/database/cologne/enable.php';
    }
);


// Obtener colonias
Flight::route("GET /@usuario_id/colognes", 
    function ($usuario_id) {
        require 'controller/database/cologne/list.php';
    }
);


Flight::route("GET /colognes/@usuario_id/@colonia_id", 
    function ($usuario_id, $colonia_id) {
       require 'controller/database/cologne/byid.php';
    }
);


Flight::route("GET /@usuario_id/colognes/dashboard/all", function ($usuario_id) {
    $login = new Login();
    $user = $login->is_user($usuario_id);
    $root = $login->is_root($usuario_id);
    
    if (!$user || !$root) {
        Flight::halt(401, "Usuario no autorizado!");
    }

    if ($user && $root) {
        $cologne = new ReadCologne();
        Flight::json($cologne);
    }
});