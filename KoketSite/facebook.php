<?php
/**
 * @author      MagePsycho <info@magepsycho.com>
 * @website     http://www.magepsycho.com
 * @category    using Header / Footer outside of Magento
 */
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
#Mage::setIsDeveloperMode(true);
#ini_set('display_errors', 1);
umask(0);
Mage::app();
Mage::getSingleton('core/session', array('name' => 'frontend'));

$block = Mage::getSingleton('core/layout');

# HEAD BLOCK
$headBlock = $block->createBlock('page/html_head'); // this wont give you the css/js inclusion
// add js
//$headBlock->addJs('prototype/prototype.js');
# add css
$headBlock->addCss('css/styles.css');
$headBlock->getCssJsHtml();
$headBlock->getIncludes();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Kokette Katinka Online Webshop</title>
    <link type="text/css" rel="stylesheet" href="https://www.kokettekatinka.be/skin/m/1414439524/skin/frontend/shopper/default/css/styles.css,/skin/frontend/base/default/css/widgets.css,/skin/frontend/base/default/css/magestore/rewardpoints.css,/skin/frontend/base/default/css/rewardpointsbehavior/behavior.css,/skin/frontend/base/default/css/rewardpointsreferfriends/referfriends.css,/skin/frontend/shopper/default/css/cloud-zoom.css,/skin/frontend/shopper/default/js/fancybox/jquery.fancybox-1.3.4.css,/skin/frontend/shopper/default/css/slider.css,/skin/frontend/shopper/default/css/local.css,/skin/frontend/shopper/default/css/responsive.css,/skin/frontend/shopper/default/css/mobile.css,/skin/frontend/shopper/default/css/animation.css,/skin/frontend/shopper/default/css/settings.css,/skin/frontend/shopper/default/css/captions.css,/skin/frontend/shopper/koket/css/override.css" media="all" />
</head>
<body>

<div class="category-products">
<ol class="products-list" id="products-list" style="padding: 20px 20px;">
<?php

$category = Mage::getModel('catalog/category')->load(31); //nice price stoffen
$category = Mage::getModel('catalog/category')->load(32); //nice price kralen
$collection = $category->getProductCollection()->addAttributeToSort('position');

//$catcount = $collection->count();
Mage::getModel('catalog/layer')->prepareProductCollection($collection);
foreach ($collection as $product) {
    ?>
    <li class="item odd">
        <div class="f-left">
            <a href="<?php echo $product->getProductUrl(); ?>" target="_blank"
               title="<?php echo $product->getName(); ?>" class="product-image">
                <div class="sale-label sale-top-right"></div>
                <img
                    src="<?php echo Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(252); ?>"
                    width="252" height="252" alt="<?php echo $product->getName(); ?>">
            </a>
        </div>
        <div class="product-shop">
            <div class="f-fix">
                <h2 class="product-name"><a href="<?php echo $product->getProductUrl(); ?>" target="_blank"
                                            title="<?php echo $product->getName(); ?>"><?php echo $product->getName(); ?></a>
                </h2>

                <div class="price-box">
                    <p class="old-price">
                            <span class="price">
                                <?php
                                $price = Mage::helper('core')->currency($product->getPrice(), true, false);
                                echo $price; ?>
                            </span>
                    </p>

                    <p class="regular-price">
                        <span class="price">
                            <?php
                            $price = Mage::helper('core')->currency($product->getSpecialPrice(), true, false);
                            echo $price; ?>
                        </span>

                    </p>


                </div>

                <div class="desc std">
                    <?php echo $product->getShortDescription() ?>

                </div>
                <p><a href="<?php echo $product->getProductUrl(); ?>" target="_blank">Meer info</a></p>

            </div>
        </div>
    </li>

<?php
}


?>
</ol>
</div>
</body>
</html>