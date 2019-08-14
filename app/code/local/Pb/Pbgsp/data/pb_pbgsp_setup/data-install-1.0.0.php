<<<<<<< HEAD
<?php
/**
 * Product:       Pb_Pbgsp (1.0.0)
 * Packaged:      2015-05-06T18:09:31+00:00
 * Last Modified: 2015-05-06T18:09:31+00:00
 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */


Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$categories = Mage::getModel('catalog/category')
    ->getCollection();
foreach($categories as $category) {
    $category->setPbPbgspUploadActive(1);
    $category->save();
}

?>
