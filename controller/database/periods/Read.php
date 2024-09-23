<?php
	class ReadPeriods
	{
	
		public function __construct()
		{}
	
	
		public function get_all_periods()
		{
			$SQL = "SELECT * FROM periodos";
			$query = Flight::gnconn()->prepare($SQL);
			$query->execute();
			$rows = $query->fetchAll();
			return $rows;
		}
	
	}