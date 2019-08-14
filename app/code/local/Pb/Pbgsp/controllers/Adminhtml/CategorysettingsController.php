<?php
/**
 * Product:       Pb_Pbgsp (1.4.0)
 * Packaged:      2016-07-28T17:25:00+00:00
 * Last Modified: 2016-07-26T14:17:00+00:00
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