<?php
/**
 * Product:       Pb_Pbgsp (1.3.7)
 * Packaged:      2016-06-01T14:02:28+00:00
 * Last Modified: 2016-04-14T14:05:10+00:00
 */

class Pb_Pbgsp_Block_Adminhtml_Categorysettings_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct()
    {
        parent::__construct();
        $this->setId('categorysettingsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        //$this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $categories = Mage::getModel('catalog/category')
            ->getCollection()

            ->addAttributeToSelect('name')
            ->addAttributeToSelect('pb_pbgsp_upload_active');
        $this->setCollection($categories);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('ID', array(
            'header'    => 'ID',
            'align'     =>'right',
            'width'     => '10px',
            'index'     => 'entity_id',
            'type'      => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => 'Name',
            'align'     =>'left',
            'index'     => 'name',
           // 'width'     => '50px',
        ));


        $this->addColumn('export', array(
            'header'    => 'Export',
            //'width'     => '150px',
            'index'     => 'pb_pbgsp_upload_active',
            'type' => 'options',
            'options' => array(1=>'Yes',0=>'No')
        ));
        return parent::_prepareColumns();
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('category');

//        $this->getMassactionBlock()->addItem('delete', array(
//            'label'=> Mage::helper('udropship')->__('Delete'),
//            'url'  => $this->getUrl('*/*/massDelete'),
//            'confirm' => Mage::helper('udropship')->__('Are you sure?')
//        ));

        $this->getMassactionBlock()->addItem('status', array(
            'label'=>'Change status',
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'status' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => 'Export ?',
                    'values' => array(1=>'Yes',0=>'No'),
                )
            )
        ));




        return $this;
    }
}