<?php
/**
 * Product:       Pb_Pbgsp (1.3.9)
 * Packaged:      2016-07-26T14:17:00+00:00
 * Last Modified: 2016-06-23T10:40:00+00:00

 */

class Pb_Pbgsp_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function getExtensionVersion()
	{
		return (string) Mage::getConfig()->getNode()->modules->Pb_Pbgsp->version;
	}

}
