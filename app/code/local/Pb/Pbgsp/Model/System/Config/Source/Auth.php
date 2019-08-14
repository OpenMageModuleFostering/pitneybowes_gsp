<?php

class pb_pbgsp_model_System_Config_Source_Auth
{

    public function toOptionArray()
    {
       return array(
            array('value'=>"Auth: https://cbs.ecommerce.pb.com/auth/token", 'label'=>'Production'),
            array('value'=>"https://sandbox-cbs.ecommerce.pb.com/auth/token", 'label'=>'Sandbox'),

        );
    }

}
