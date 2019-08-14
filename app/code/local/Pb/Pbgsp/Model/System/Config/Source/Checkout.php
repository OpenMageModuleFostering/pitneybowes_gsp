<?php

class pb_pbgsp_model_System_Config_Source_Checkout
{
	
    public function toOptionArray()
    {
    	 return array(
            array('value'=>"https://cbs.ecommerce.pb.com/checkout/services/v1", 'label'=>'Production'),
            array('value'=>"https://sandbox-cbs.ecommerce.pb.com/checkout/services/v1", 'label'=>'Sandbox'),

        );
    }

}
