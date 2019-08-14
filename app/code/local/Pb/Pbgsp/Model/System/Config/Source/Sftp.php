<?php

class pb_pbgsp_model_System_Config_Source_Sftp
{
	
    public function toOptionArray()
    {
		return array(
            array('value'=>"sftp-cbs.ecommerce.pb.com", 'label'=>'Production'),
            array('value'=>"sftp-sandbox-cbs.ecommerce.pb.com", 'label'=>'Sandbox'),

        );
    }

}
