<?php

class pb_pbgsp_model_System_Config_Source_Asn
{
	
    public function toOptionArray()
    {
    	 return array(
            array('value'=>"https://cbs.ecommerce.pb.com/order-mgmt/services/v1", 'label'=>'Production'),
            array('value'=>"https://op-sandbox-cbs.ecommerce.pb.com/order-mgmt/services/v1", 'label'=>'Sandbox'),

        );
    }

}
