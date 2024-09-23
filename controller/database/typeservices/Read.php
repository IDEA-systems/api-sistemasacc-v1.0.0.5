<?php
	class ReadTypeServices
	{
	
		public function __construct()
		{}
	
	
		public function get_all_services()
		{
			$SQL = "SELECT * FROM tipo_servicios";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
	
	}