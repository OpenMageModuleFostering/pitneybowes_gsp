<?php
/**
 * Product:       Pb_Pbgsp (1.1.2)
 * Packaged:      2015-09-23T12:09:53+00:00
 * Last Modified: 2015-09-14T12:11:20+00:00




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
