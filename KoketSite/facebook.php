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

$_helper    = Mage::helper('catalog/output');


function isNew($product)
{
    if ($product->getData('featured_product')) {
        return true;
    }

    if ($product->getData('news_from_date') == null && $product->getData('news_to_date') == null) {
        return false;
    }

    if ($product->getData('news_from_date') !== null) {
        if (date('Y-m-d', strtotime($product->getData('news_from_date'))) > date('Y-m-d', time())) {
            return false;
        }
    }

    if ($product->getData('news_to_date') !== null) {
        if (date('Y-m-d', strtotime($product->getData('news_to_date'))) < date('Y-m-d', time())) {
            return false;
        }
    }

    return true;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Kokette Katinka - Kralen, Stoffen en Juwelen</title>
    <link type="text/css" rel="stylesheet" href="http://www.kokettekatinka.be/skin/m/1414439524/skin/frontend/shopper/default/css/styles.css,/skin/frontend/base/default/css/widgets.css,/skin/frontend/base/default/css/magestore/rewardpoints.css,/skin/frontend/base/default/css/rewardpointsbehavior/behavior.css,/skin/frontend/base/default/css/rewardpointsreferfriends/referfriends.css,/skin/frontend/shopper/default/css/cloud-zoom.css,/skin/frontend/shopper/default/js/fancybox/jquery.fancybox-1.3.4.css,/skin/frontend/shopper/default/css/slider.css,/skin/frontend/shopper/default/css/local.css,/skin/frontend/shopper/default/css/responsive.css,/skin/frontend/shopper/default/css/mobile.css,/skin/frontend/shopper/default/css/animation.css,/skin/frontend/shopper/default/css/settings.css,/skin/frontend/shopper/default/css/captions.css,/skin/frontend/shopper/koket/css/override.css" media="all" />
    <link href='https://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:300,400,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Alegreya:300,400,700' rel='stylesheet' type='text/css'>
</head>
<body>

<div class="page-title-bg" style="position: inherit;background-color: #eefaed">
    <h1 style="padding: 30px 0;font-family: 'Yanone Kaffeesatz',Tahoma,sans-serif;text-transform: uppercase">Een greep uit onze nieuwste producten in <a href="http://www.kokettekatinka.be" target="koket">de webshop</a></h1>
</div>

<div class="category-products">
<ol class="products-list" id="products-list" style="padding: 20px 20px;">
<?php

$collection = Mage::getModel('catalog/category')->load(2)
    ->getProductCollection()
    ->addAttributeToSelect('*')
    ->addFieldToFilter('visibility', array(
        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
    )) //showing just products visible in catalog or both search and catalog
    ->addAttributeToSort('created_at', 'DESC')
    ->setPageSize(150) // limit number of results returned
    ->setCurPage(0);

foreach ($collection as $product) {
    if (!strpos($product->getSku(), "sample") && isNew($product)) {
    ?>
    <li class="item odd">
        <div class="f-left">
            <a href="<?php echo $product->getProductUrl(); ?>" target="koket"
               title="<?php echo $product->getName(); ?>" class="product-image">
                <?php if ($product->getSpecialPrice() > 0) { ?>
                    <div class="sale-label sale-top-right"></div>
                <?php } ?>
                <?php  if (isNew($product)) { ?>
                    <div class="new-label new-top-left"></div>
                <?php } ?>
                <img
                    src="<?php echo Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(252); ?>"
                    width="252" height="252" alt="<?php echo $product->getName(); ?>">
            </a>
        </div>
        <div class="product-shop">
            <div class="f-fix">
                <h2 class="product-name"><a href="<?php echo $product->getProductUrl(); ?>" target="koket"  style="font-family: 'Yanone Kaffeesatz',Tahoma,sans-serif; font-size: 18px;"
                                            title="<?php echo $product->getName(); ?>"><?php echo $product->getName(); ?></a>
                </h2>

                <div class="price-box">
                    <?php if ($product->getSpecialPrice() > 0) { ?>
                        <p class="old-price">
                                <span class="price" style="font-family: Alegreya,Tahoma,sans-serif;">
                                    <?php
                                    $price = Mage::helper('core')->currency($product->getPrice(), true, false);
                                    echo $price; ?>
                                </span>
                        </p>
                    <?php } ?>
                    <p class="regular-price">
                        <span class="price"  style="font-family: Alegreya,Tahoma,sans-serif;">
                            <?php
                            if ($product->getSpecialPrice() > 0) {
                                $price = $product->getSpecialPrice();
                            } else {
                                $price = $product->getPrice();
                            }
                            $price = Mage::helper('core')->currency($price, true, false);
                            echo $price; ?>
                        </span>

                    </p>


                </div>

                <div class="desc std">
                    <?php
                        echo $_helper->productAttribute($product, nl2br($product->getShortDescription()), 'short_description') ?>
                </div>
                <p><a href="<?php echo $product->getProductUrl(); ?>" target="koket">Meer info</a></p>

            </div>
        </div>
    </li>

<?php
    }//if
}


?>
</ol>
</div>

<p style="margin-bottom: 40px;">Je vindt nog meer nieuwe producten op onze site <a href="http://www.kokettekatinka.be" target="koket">www.kokettekatinka.be</a></p>

</body>
</html>