<?php

	function IPv4_generator($segment = 16, $range = 1, $hosts = 254, $ips = []) {
		for ($i = 1; $i < $hosts; $i++) {
			$range++;
			if ($range > 254) {
				$segment++;
				$range = 1;
			}
			$ips[$i] = "192.168.$segment.$range";
		}
		return $ips;
	}

?>