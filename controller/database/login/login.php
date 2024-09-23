<?php

session_start();
$body = Flight::request()->getBody();
$request = json_decode($body, true);

$usuario_nombre = isset($_request['user_name']) ? $_request['user_name'] : '';
$usuario_password = isset($_request['user_password']) ? $_request['user_password'] : '';

$SQL = "SELECT usuarios.usuario_id, usuarios.usuario_status, usuarios.usuario_perfil, usuarios.usuario_nombre, usuarios.usuario_password, usuarios.usuario_tipo, usuarios_tipos.tipo_id, usuarios_tipos.tipo_nombre FROM usuarios INNER JOIN usuarios_tipos ON usuarios.usuario_tipo = usuarios_tipos.tipo_id WHERE usuario_nombre = '$usuario_nombre'";
$query = Flight::gnconn()->prepare($SQL);
$query->execute();
$rows = $query->fetchAll();

if (count($rows) == 0) {

	Flight::json(array(
		"status" => 204,
		"title" => "¡Datos vacios!",
		"details" => "¡No encotramos datos!",
		"data" => []
	));

} else {

	$_SESSION['userid'] = $rows;

	Flight::json(array(
		"status" => 200,
		"title" => "¡Datos encontrados!",
		"details" => "¡Más de una fila encontrada!",
		"data" => $_SESSION['userid']
	));

}

?>