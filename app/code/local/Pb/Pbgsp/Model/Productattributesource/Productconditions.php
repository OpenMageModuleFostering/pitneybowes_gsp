<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00

 */

class Pb_Pbgsp_Model_Productattributesource_Productconditions extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    const NEW_ = 'New';
    const USED = 'Used';
    const REFURBISHED = 'Refurbished';

    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => '',
                    'value' =>  ''
                ),
                array(
                    'label' => Mage::helper('pbgsp')->__('New'),
                    'value' =>  self::NEW_
                ),
                array(
                    'label' => Mage::helper('pbgsp')->__('Used'),
                    'value' =>  self::USED
                ),
                array(
                    'label' => Mage::helper('pbgsp')->__('Refurbished'),
                    'value' =>  self::REFURBISHED
                ),
            );
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    public function addValueSortToCollection($collection, $dir = 'asc')
    {
        $adminStore  = Mage_Core_Model_App::ADMIN_STORE_ID;
        $valueTable1 = $this->getAttribute()->getAttributeCode() . '_t1';
        $valueTable2 = $this->getAttribute()->getAttributeCode() . '_t2';

        $collection->getSelect()->joinLeft(
            array($valueTable1 => $this->getAttribute()->getBackend()->getTable()),
            "`e`.`entity_id`=`{$valueTable1}`.`entity_id`"
            . " AND `{$valueTable1}`.`attribute_id`='{$this->getAttribute()->getId()}'"
            . " AND `{$valueTable1}`.`store_id`='{$adminStore}'",
            array()
        );

        if ($collection->getStoreId() != $adminStore) {
            $collection->getSelect()->joinLeft(
                array($valueTable2 => $this->getAttribute()->getBackend()->getTable()),
                "`e`.`entity_id`=`{$valueTable2}`.`entity_id`"
                . " AND `{$valueTable2}`.`attribute_id`='{$this->getAttribute()->getId()}'"
                . " AND `{$valueTable2}`.`store_id`='{$collection->getStoreId()}'",
                array()
            );
            $valueExpr = new Zend_Db_Expr("IF(`{$valueTable2}`.`value_id`>0, `{$valueTable2}`.`value`, `{$valueTable1}`.`value`)");

        } else {
            $valueExpr = new Zend_Db_Expr("`{$valueTable1}`.`value`");
        }



        $collection->getSelect()
            ->order($valueExpr, $dir);

        return $this;
    }

    public function getFlatColums()
    {
        $columns = array(
            $this->getAttribute()->getAttributeCode() => array(
                'type'      => 'varchar',
                'unsigned'  => false,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            )
        );
        return $columns;
    }


    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }

}