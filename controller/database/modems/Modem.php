<?php


  class Modems {

    public $marca;                // Marca del modem
    public $modelo;               // Modelo del modem
    public $idmodem;              // {ID} del modem
    public $fotomodem;            // Foto del modem
    public $error_message;        // Detalles del error
    public $error;                // true o false


    public function __construct($request = []) {
      $this->marca = isset($request->marca) ? $request->marca : null;
      $this->modelo = isset($request->modelo) ? $request->modelo : null;
      $this->fotomodem = isset($request->fotomodem) ? $request->fotomodem : null;
    }


    public function search_repeat_modem($modelo) {
      $this->modelo = $modelo;
      $sql = "SELECT idmodem FROM modem WHERE modelo = '$this->modelo'";
      $query = Flight::gnconn()->prepare($sql);
      $query->execute();
      $rows = $query->fetchAll();
      $is_exists = !empty($rows);
      return $is_exists;
    }


    public function Create() {
      if ($this->search_repeat_modem($this->modelo)) {
        $this->error = true;
        $this->error_message = "Modelo existente!";
        return false;
      }
      $sql = "INSERT INTO modem VALUES (null, '$this->marca', '$this->modelo', '$this->fotomodem')";
      $query = Flight::gnconn()->prepare($sql);
      $query->execute();
      $error = intval($query->errorCode());
      if ($error != 0) {
        $this->error = true;
        $this->error_message = "Error al agregar modem!";
        return false;
      }
      $this->idmodem = Flight::gnconn()->lastInsertId();
      return true;
    }


    public function get_modem_by_id($idmodem) {
      $this->idmodem = $idmodem;
      $sql = "SELECT * FROM modem WHERE idmodem = $this->idmodem";
      $query = Flight::gnconn()->prepare($sql);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

  }


?>