<?php
/**
 * Product:       Pb_Pbgsp (1.0.2)
 * Packaged:      2015-09-25T15:12:28+00:00
 * Last Modified: 2015-09-21T15:12:31+00:00


 * File:          app/code/local/Pb/Pbgsp/Model/Inboundparcel.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Inboundparcel extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('pb_pbgsp/inboundparcel');
    }
}

?>
