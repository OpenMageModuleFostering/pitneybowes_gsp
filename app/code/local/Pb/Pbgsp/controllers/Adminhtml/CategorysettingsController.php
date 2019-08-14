<?php
/**
 * Product:       Pb_Pbgsp (1.3.7)
 * Packaged:      2016-06-01T14:02:28+00:00
 * Last Modified: 2016-04-14T14:05:10+00:00
 */

class Pb_Pbgsp_Adminhtml_CategorysettingsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout();
        //$this->_setActiveMenu('sales/udropship');
       // $this->_addBreadcrumb($hlp->__('Vendors'), $hlp->__('Vendors'));
        $this->_addContent($this->getLayout()->createBlock('PB_Pbgsp/adminhtml_categorysettings'));

        $this->renderLayout();
    }

    public function massStatusAction()
    {
        $modelIds = (array)$this->getRequest()->getParam('category');
        $status     = (string)$this->getRequest()->getParam('status');

        try {
            foreach ($modelIds as $modelId) {
                Mage::getModel('catalog/category')->load($modelId)->setPbPbgspUploadActive($status)->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully updated', count($modelIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('There was an error while updating category(s) export status'));
        }

        $this->_redirect('*/*/');
    }

}