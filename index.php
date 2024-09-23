<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: x-api-key, Content-Type, Authorization');
    die();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, UPDATE");
header("Allow: GET, POST, PUT, DELETE, UPDATE");

// Usar Flight
require 'flight/Flight.php';
// DB connection
require 'controller/database/connection/index.php';
// Usar mikrotik class
require 'mkt/Mikrotik.php';
// Messenger
require 'controller/messenger/Messenger.php';
// Rutas de finalizar servicios
require "controller/database/process/index.php";
// Reintentar procesos
require "controller/database/retry/index.php";
// Rutas de los clientes
require "controller/database/customers/index.php";
// Rutas para las antenas ap
require "controller/database/addresses/index.php";
// Rutas para las olts
require "controller/database/olt/index.php";
// Ruta para paquetes
require "controller/database/package/index.php";
// Rutas de pagos
require "controller/database/payment/index.php";
// Rutas de servicios
require "controller/database/services/index.php";
// Rutas de reportes de fallas
require "controller/database/failures/index.php";
// Rutas de reportes de instalacion
require "controller/database/instalations/index.php";
// Rutas para los horarios
require "controller/database/schedules/index.php";
// Ruta para obtener las configuraciones
require "controller/database/brand/index.php";
// Rutas de empleados
require "controller/database/employees/index.php";
// Rutas para las negociaciones
require "controller/database/negociation/index.php";
// Rutas para la caja
require "controller/database/finance/index.php";
// Routes of dashboard
require "controller/database/dashboard/index.php";
// Rutas para db mikrotik
require "controller/database/mikrotik/index.php";
// Rutas para las colonias
require "controller/database/cologne/index.php";
// Metodos de administracion
require "controller/database/methods/index.php";
// Rutas para los modems
require "controller/database/modems/index.php";
// Rutas para los status
require 'controller/database/status/index.php';
// Rutas para cortes
require 'controller/database/cuts/index.php';
// Rutas para periodos
require 'controller/database/periods/index.php';
// Rutas para tipos de servicios
require 'controller/database/typeservices/index.php';
// Rutas para las antennas
require "controller/database/antenna/index.php";
// Rutas para los usuarios
require "controller/database/users/index.php";
// Rutas para los equipos
require "controller/database/equipment/index.php";
// Rutas de mikrotik
require "controller/routeros/index.php";
// Iniciar flight
Flight::start();
