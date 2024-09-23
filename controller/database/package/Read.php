<?php

  class ReadPackages {

    public function get_all_packages() {
      $SQL = "SELECT * FROM paquetes ORDER BY idpaquete DESC";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

    public function get_package_by_id($idpaquete) {
      $SQL = "SELECT * FROM paquetes WHERE idpaquete = $idpaquete ORDER BY idpaquete DESC";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

  }

?>