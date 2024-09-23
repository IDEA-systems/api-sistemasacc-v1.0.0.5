<?php


class ReadUser {

  public function __construct() {}

  public function get_all_users() {
    $query = Flight::gnconn()->prepare("
      SELECT usuarios.*, empleados.* 
      FROM usuarios 
      INNER JOIN empleados 
      ON usuarios.usuario_id = empleados.usuario_id 
      INNER JOIN usuarios_tipos 
      ON usuarios.usuario_tipo = usuarios_tipos.tipo_id 
      INNER JOIN usuarios_status 
      ON usuarios.usuario_status = usuarios_status.status_id
    ");
    $query->execute();
    $rows = $query->fetchAll();
    return $rows;
  }

  public function get_user_by_id($usuario_id) {
    $SQL = "SELECT * FROM usuarios LEFT JOIN empleados ON usuarios.usuario_id = empleados.usuario_id LEFT JOIN usuarios_tipos ON usuarios.usuario_tipo = usuarios_tipos.tipo_id LEFT JOIN usuarios_status ON usuarios.usuario_status = usuarios_status.status_id WHERE usuarios.usuario_id = '$usuario_id'";
    $query = Flight::gnconn()->prepare($SQL);
    $query->execute();
    $rows = $query->fetchAll();
    return $rows;
  }

  public function get_type_users() {
    $SQL = "SELECT * FROM usuarios_tipos ORDER BY tipo_id DESC";
    $query = Flight::gnconn()->prepare($SQL);
    $query->execute();
    $rows = $query->fetchAll();
    return $rows;
  }

}


?>