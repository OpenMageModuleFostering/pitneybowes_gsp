<?php
/**
 * Product:       Pb_Pbgsp (1.2.1)
 * Packaged:      2015-10-07T12:08:45+00:00
 * Last Modified: 2015-10-01T12:11:15+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Ordernumber.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Ordernumber extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('pb_pbgsp/ordernumber');
    }
}

?>
