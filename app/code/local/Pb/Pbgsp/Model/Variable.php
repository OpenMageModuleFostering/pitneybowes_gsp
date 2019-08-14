<?php
/**
 * Product:       Pb_Pbgsp (1.4.0)
 * Packaged:      2016-07-28T17:25:00+00:00
 * Last Modified: 2016-07-26T14:17:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Variable.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Variable extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('pb_pbgsp/variable');
    }
}

?>
