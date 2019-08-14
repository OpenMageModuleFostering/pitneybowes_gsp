<?php
/**
 * Product:       Pb_Pbgsp (1.2.3)
 * Packaged:      2015-11-04T12:13:20+00:00
 * Last Modified: 2015-10-21T12:09:20+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Variable.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Variable extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('pb_pbgsp/variable');
    }
}

?>
