<?php
require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'frontend'));

$cat_id = 18; //koopjes
$category = Mage::getModel('catalog/category')->load($cat_id);
$collection = $category->getProductCollection()->addAttributeToSort('position');
//$catcount = $collection->count();
Mage::getModel('catalog/layer')->prepareProductCollection($collection);
foreach ($collection as $product) {
    echo $product->getName();
    echo $product->getPrice();
    echo Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(260);
    echo $product->getProductUrl();
}


?>