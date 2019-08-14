<?php
/**
 * Product:       Pb_Pbgsp (1.4.0)
 * Packaged:      2016-07-28T17:25:00+00:00
 * Last Modified: 2016-07-26T14:17:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Environmentconfig.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Environmentconfig{
	
    public function toOptionArray()
    {
        return array(
            array('value'=>"0", 'label'=>'Select'),
			array('value'=>"1", 'label'=>'Sandbox'),
			array('value'=>"2", 'label'=>'Production'),
          

        );
    }
	
	
}