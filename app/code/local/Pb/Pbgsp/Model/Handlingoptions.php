<?php
/**
 * Product:       Pb_Pbgsp (1.2.3)
 * Packaged:      2015-11-04T12:13:20+00:00
 * Last Modified: 2015-10-21T12:09:20+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Handlingoptions.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Handlingoptions {
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>'Per Order'),
            array('value'=>2, 'label'=>'Per Item'),

        );
    }
}