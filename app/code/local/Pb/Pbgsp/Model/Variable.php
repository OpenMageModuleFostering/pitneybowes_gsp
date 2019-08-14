<?php
/**
 * Product:       Pb_Pbgsp (1.0.3)
 * Packaged:      2015-09-1T15:12:28+00:00
 * Last Modified: 2015-08-25T15:12:28+00:00



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
