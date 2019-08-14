<?php
/**
 * Product:       Pb_Pbgsp (1.0.1)
 * Packaged:      2015-09-21T15:12:31+00:00
 * Last Modified: 2015-06-04T15:00:31+00:00

 * File:          app/code/local/Pb/Pbgsp/Model/Helper.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Helper {
	public static function displayInfo($var) {
		echo "<hr/>Class type: ".get_class($var)."<br/>Methods:<br/>";
		$methods = get_class_methods($var);
		foreach ($methods as $method) {
			echo $method."<br/>";
		}
	}
}
?>
