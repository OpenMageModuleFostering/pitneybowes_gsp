<?php
/**
 * Product:       Pb_Pbgsp (1.4.1)
 * Packaged:      2016-07-26T14:25:00+00:00
 * Last Modified: 2016-09-13T10:50:00+00:00


 */



class Pb_Pbgsp_Block_Adminhtml_Version extends  Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return (string) Mage::helper('pbgsp')->getExtensionVersion();
    }
}
?>