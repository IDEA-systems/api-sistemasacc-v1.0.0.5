<!-- obtener las notificaciones -->
<?php

  // CLIENTES CON INSTALCIONES PENDIENTES PARA EL DIA DE HOY //
  $SQL = "SELECT * FROM clientes INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id CROSS JOIN detalles_instalacion ON clientes_servicios.cliente_id = detalles_instalacion.cliente_id WHERE clientes_servicios.cliente_status = 0 OR detalles_instalacion.fecha_agenda = CURRENT_DATE()";
  $query = Flight::gnconn()->prepare($SQL);
  $query->execute();
  $instalaciones = $query->fetchAll();

  // NEGOCIACIONES PENDIENTES //
  $SQL = "SELECT * FROM negociaciones WHERE fecha_fin = CURRENT_DATE()";
  $query = Flight::gnconn()->prepare($SQL);
  $query->execute();
  $negociaciones = $query->fetchAll();

  // CLIENTES A LOS QUE SE LES TERMINA EL MES GRATIS EL DIA DE HOY //
  $SQL = "SELECT * FROM clientes INNER JOIN clientes_servicios ON clientes.cliente_id = clientes_servicios.cliente_id WHERE MONTH(clientes.cliente_instalacion) = MONTH(CURRENT_DATE()) - 1 AND DAY(clientes.cliente_instalacion) = DAY(CURRENT_DATE()) AND YEAR(clientes.cliente_instalacion) = YEAR(CURRENT_DATE()) AND clientes_servicios.cliente_status = 6";
  $query = Flight::gnconn()->prepare($SQL);
  $query->execute();
  $nuevos = $query->fetchAll();

  Flight::json(
    array(
      "status" => 200,
      "title" => "Datos obtenidos!",
      "details" => "Los datos fueron procesados!",
      "data" => array(
        "nuevos" => $nuevos,
        "instalaciones" => $instalaciones,
        "negociaciones" => $negociaciones
      )
    )
  );

?>