<?php

//update the color codes for a given product id
//IMPORTANT: attribute scope must be set to "global"!

$id = htmlspecialchars($_GET["id"]);
$colors = htmlspecialchars($_GET["colors"]);
$hueList = explode(",", $_GET["hueList"]);

$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app();


$product = Mage::getModel('catalog/product')->load($id);

$product->setColorsHsl($colors);

for ($i = 0; $i < 5; $i++) {
    if ($hueList[$i] != ""){
        $product->setData("color_hue".($i+1), $hueList[$i]);
    } else {
        $product->setData("color_hue".($i+1), "");
    }
}



$resource = $product->getResource();

$resource->saveAttribute($product,'colors_hsl');


for ($i = 0; $i < 5; $i++) {
    $resource->saveAttribute($product,'color_hue'.($i+1));
}

echo $resource->getAttributeRawValue($product->getId(), 'colors_hsl', 1);
echo "- hue1: " . $resource->getAttributeRawValue($product->getId(), 'color_hue1', 1);
echo "- hue2: " . $resource->getAttributeRawValue($product->getId(), 'color_hue2', 1);
echo "- hue3: " . $resource->getAttributeRawValue($product->getId(), 'color_hue3', 1);
echo "- hue4: " . $resource->getAttributeRawValue($product->getId(), 'color_hue4', 1);
echo "- hue5: " . $resource->getAttributeRawValue($product->getId(), 'color_hue5', 1);


