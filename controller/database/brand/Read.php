<?php

  class ReadBrand 
  {

    public $config;
    public function __construct() 
    {
      // $this->config = $this->get_config();
    }


    public function get_config() {
      $SQL = "SELECT * FROM configuration";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

    public function get_messages() {
      $SQL = "SELECT * FROM mensajeria";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

    public function get_information_brand() {
      $SQL = "SELECT * FROM brand_information";
      $query = Flight::gnconn()->prepare($SQL);
      $query->execute();
      $rows = $query->fetchAll();
      return $rows;
    }

  }