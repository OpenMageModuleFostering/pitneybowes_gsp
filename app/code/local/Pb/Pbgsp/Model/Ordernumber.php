<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Ordernumber.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Ordernumber extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('pb_pbgsp/ordernumber');
    }
}

?>
