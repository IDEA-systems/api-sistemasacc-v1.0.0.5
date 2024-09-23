<?php

$config = parse_ini_file('./config.ini', true);
$DRIVER = isset($config['DRIVER']) ? $config['DRIVER'] : 'mysql';
$HOST = isset($config['HOST']) ? $config['HOST'] : 'localhost';
$DATABASE = isset($config['DATABASE']) ? $config['DATABASE'] : '';
$USER = isset($config['USER']) ? $config['USER'] : 'root';
$PASSWORD = isset($config['PASSWORD']) ? $config['PASSWORD'] : '';
Flight::register('gnconn', 'PDO', ["$DRIVER:host=$HOST;dbname=$DATABASE", $USER, $PASSWORD]);
