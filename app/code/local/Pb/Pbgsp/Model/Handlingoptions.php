<?php
/**
 * Created by JetBrains PhpStorm.
 * User: muhammad.kamran
 * Date: 6/6/12
 * Time: 11:36 PM
 * To change this template use File | Settings | File Templates.
 */
class Pb_Pbgsp_Model_Handlingoptions {
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>'Per Order'),
            array('value'=>2, 'label'=>'Per Item'),

        );
    }
}