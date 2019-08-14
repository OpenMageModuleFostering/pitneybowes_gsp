<?php
/**
 * Product:       Pb_Pbgsp (1.3.9)
 * Packaged:      2016-07-26T14:17:00+00:00
 * Last Modified: 2016-06-23T10:40:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Handlingoptions.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
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