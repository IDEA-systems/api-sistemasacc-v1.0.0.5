<?php
  require 'controller/database/employees/Read.php';
  require 'controller/database/employees/Employees.php';
  
  Flight::route("GET /@usuario_id/employees", function() {
    $employees = new ReadEmployees();
    $data = $employees->get_all_employees();
    Flight::json($data);
  });


  Flight::route("GET /@usuario_id/promotors", function() {
    $employees = new ReadEmployees();
    $data = $employees->get_all_promotors();
    Flight::json($data);
  });