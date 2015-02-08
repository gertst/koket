<?php

//update the color codes for a given product id

$id = htmlspecialchars($_GET["id"]);
$colors = htmlspecialchars($_GET["colors"]);

$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app();


$product = Mage::getModel('catalog/product')->load($id);

$product->setData('color_codes', $colors);
$_resource = $product->getResource();
$_resource->saveAttribute($product, 'color_codes');

echo $_resource->getAttributeRawValue($product->getId(), 'color_codes', 1);