<?php
/**
 * Product:       Pb_Pbgsp (1.0.3)
 * Packaged:      2015-09-1T15:12:28+00:00
 * Last Modified: 2015-08-25T15:12:28+00:00



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
