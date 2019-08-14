<?php
/**
 * Product:       Pb_Pbgsp (1.4.1)
 * Packaged:      2016-07-26T14:25:00+00:00
 * Last Modified: 2016-09-13T10:50:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Handlingoptions.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Newhandlingoptions {
	
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>'Per Order'),
            array('value'=>2, 'label'=>'Per Item'),

        );
	
		
    }
}