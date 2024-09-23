<?php

class Services
{

	public $error;
	public $conflict;
	public $error_message;
	public $servicio_id;
	public $nombre_servicio;
	public $costo_servicio;
	public $status;

	public function __construct($request = []) 
	{
		$this->servicio_id = isset($request->servicio_id) ? $request->servicio_id : null;
		$this->nombre_servicio = isset($request->nombre_servicio) ? $request->nombre_servicio : null;
		$this->costo_servicio = isset($request->costo_servicio) ? $request->costo_servicio : 0;
		$this->status = isset($request->status) ? $request->status : "activo";
	}

	
	/**
	 * create
	 *
	 * @return void
	 */
	public function create() : void
	{
		$is_register = $this->is_register();
		if ($is_register) {
			$this->conflict = true;
			$this->error_message = "El nombre de servicio ya existe!";
		}
		if (!$is_register) {
			$this->insert_into_servicios();
		}
	}


	public function update() : void
	{
		$is_register = $this->is_register();
		if ($is_register) {
			$this->conflict = true;
			$this->error_message = "El nombre de servicio ya existe!";
		}
		if (!$is_register) {
			$this->update_servicios();
		}
	}

	
	/**
	 * delete
	 *
	 * @return void
	 */
	public function delete($servicio_id) : void
	{
		$is_register = $this->being_used();
		if ($is_register) {
			$this->conflict = true;
			$this->error_message = "Hay clientes con este servicio activo!";
		}
		if (!$is_register) {
			$this->disabled_service($servicio_id);
		}
	}



	public function disabled_service($servicio_id) : void
	{
		try {
			$this->status = 'inactivo';
			$this->servicio_id = $servicio_id;

			$query = Flight::gnconn()->prepare("
				UPDATE servicios
				SET status = ?
				WHERE servicio_id = ?
			");

			$query->execute([
				$this->status,
				$this->servicio_id
			]);

		} catch (Exception $error) {
			$this->error = true;
			$this->error = "Error al deshabilitar el servicio!";
		}
	}


	public function reactive($servicio_id) : void
	{
		try {
			$this->status = 'activo';
			$this->servicio_id = $servicio_id;

			$query = Flight::gnconn()->prepare("
				UPDATE servicios
				SET status = ?
				WHERE servicio_id = ?
			");

			$query->execute([
				$this->status,
				$this->servicio_id
			]);

		} catch (Exception $error) {
			$this->error = true;
			$this->error = "Error al habilitar el servicio!";
		}
	}

	
	/**
	 * insert_into_servicios
	 *
	 * @return void
	 */
	public function insert_into_servicios() : void
	{
		try {
			$query = Flight::gnconn()->prepare("
				INSERT INTO `servicios` 
				VALUES(?, ?,?,?)
			");
			$query->execute([
				$this->servicio_id,
				$this->nombre_servicio,
				$this->costo_servicio,
				$this->status
			]);
			$this->servicio_id = $this->get_id();
		} catch (Exception $error) {
			$this->error = true;
			$this->error = "Error al agregar el servicio!";
		}
	}



	public function update_servicios() : void
	{
		try {
			$query = Flight::gnconn()->prepare("
				UPDATE servicios
				SET nombre_servicio = ?,
				costo_servicio = ?,
				status = ?
				WHERE servicio_id = ?
			");
			$query->execute([
				$this->nombre_servicio,
				$this->costo_servicio,
				$this->status,
				$this->servicio_id
			]);
		} catch (Exception $error) {
			$this->error = true;
			$this->error = "Error al actualizar el servicio!";
		}
	}


	
	/**
	 * is_register
	 *
	 * @return bool
	 */
	public function is_register() : bool
	{
		$query = Flight::gnconn()->prepare("
			SELECT servicio_id 
			FROM `servicios` 
			WHERE nombre_servicio = LOWER(?)
			AND servicio_id != ?
		");
		$query->execute([ 
			$this->nombre_servicio, 
			$this->servicio_id 
		]);
		$rows = $query->fetchAll();
		$result = count($rows) >= 1;
		return $result;
	}


	public function being_used() : bool
	{
		$query = Flight::gnconn()->prepare("
			SELECT cliente_id 
			FROM `servicios_adicionales` 
			WHERE servicio_id = ?
			AND status_servicio IN ('activo', 'pausado')
		");
		$query->execute([ $this->servicio_id ]);
		$rows = $query->fetchAll();
		$result = count($rows) >= 1;
		return $result;
	}

	
	/**
	 * get_all_services
	 *
	 * @return array
	 */
	public function get_all_services() : array
	{
		$SQL = "SELECT * FROM servicios ORDER BY servicio_id ASC";
        $query = Flight::gnconn()->prepare($SQL);
        $query->execute();
        $rows = $query->fetchAll();
        return $rows;
	}


	
	/**
	 * get_services_by_id
	 *
	 * @param  mixed $servicio_id
	 * @return array
	 */
	public function get_services_by_id($servicio_id) : array
	{
        $query = Flight::gnconn()->prepare("
			SELECT * FROM servicios 
			WHERE servicio_id = ?
		");
        $query->execute([ $servicio_id ]);
        $rows = $query->fetchAll();
        return $rows;
	}


	
	/**
	 * get_id
	 *
	 * @return int
	 */
	public function get_id() : int
	{
		return Flight::gnconn()->lastInsertId();
	}
}