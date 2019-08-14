<?php
/**
 * Product:       Pb_Pbgsp (1.3.8)
 * Packaged:      2016-06-23T10:40:00+00:00
 * Last Modified: 2016-06-01T14:02:28+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Environmentconfig.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Phonetype{
	
    public function toOptionArray()
    {
        return array(
            array('value'=>"mobile", 'label'=>'Mobile'),
			array('value'=>"home", 'label'=>'Home'),
			array('value'=>"work", 'label'=>'Work'),
          

        );
    }
	
	
}