<?php
/**
 * Product:       Pb_Pbgsp (1.3.9)
 * Packaged:      2016-07-26T14:17:00+00:00
 * Last Modified: 2016-06-23T10:40:00+00:00
 */

class Pb_Pbgsp_Block_Adminhtml_Categorysettings extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct()
    {
        $this->_controller = 'adminhtml_categorysettings';
        $this->_blockGroup = 'PB_Pbgsp';
        $this->_headerText = 'Category Export Settings';
        //$class = Mage::getConfig()->getBlockClassName($this->_blockGroup.'/' . $this->_controller . '_grid');
        //var_dump($class);
        parent::__construct();
        $this->_removeButton('add');
    }

}