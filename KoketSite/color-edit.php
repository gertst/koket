<?php
/**
 * @author      MagePsycho <info@magepsycho.com>
 * @website     http://www.magepsycho.com
 * @category    using Header / Footer outside of Magento
 */
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
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

$storeId = 1;

$_helper    = Mage::helper('catalog/output');



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Color editor</title>
    <link type="text/css" rel="stylesheet" href="http://www.kokettekatinka.be/skin/m/1414439524/skin/frontend/shopper/default/css/styles.css,/skin/frontend/base/default/css/widgets.css,/skin/frontend/base/default/css/magestore/rewardpoints.css,/skin/frontend/base/default/css/rewardpointsbehavior/behavior.css,/skin/frontend/base/default/css/rewardpointsreferfriends/referfriends.css,/skin/frontend/shopper/default/css/cloud-zoom.css,/skin/frontend/shopper/default/js/fancybox/jquery.fancybox-1.3.4.css,/skin/frontend/shopper/default/css/slider.css,/skin/frontend/shopper/default/css/local.css,/skin/frontend/shopper/default/css/responsive.css,/skin/frontend/shopper/default/css/mobile.css,/skin/frontend/shopper/default/css/animation.css,/skin/frontend/shopper/default/css/settings.css,/skin/frontend/shopper/default/css/captions.css,/skin/frontend/shopper/koket/css/override.css" media="all" />
    <link href='https://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:300,400,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Alegreya:300,400,700' rel='stylesheet' type='text/css'>
</head>
<body>

<script src="/js/queldorei/jquery-1.8.2.min.js"></script>
<script src="/js/stogo/mustache.js"></script>
<script src="/js/stogo/color-thief.js"></script>
<script src="/js/stogo/colours.js"></script>
<script src="/js/stogo/color-edit.js?<?php echo rand()?>"></script>

<script id="color-thief-output-template" type="text/x-mustache">
    <div class="function get-palette">
        <div class="swatches" data-id="{{id}}" >
            {{#palette}}
            <div class="swatch" style="background-color: rgb({{0}}, {{1}}, {{2}})"></div>
            {{/palette}}
        </div>
        <div><a class="add-swatch" href="#">Add swatch</a></div>

    </div>
</script>


<style type="text/css">
    .swatches {
        width: 250px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    .swatch {
        width: 31px;
        height: 2rem;
        display: table-cell;
        margin: 0;
        background: #ddd;
        border: #ffffff solid 2px;
    }
    .swatch.selected {
        border: #1F1F1F solid 2px;
    }
    .category-products {
        text-align: center;
    }
    .item {
        padding-bottom: 50px;
    }
</style>

<div class="page-title-bg" style="position: inherit;background-color: #eefaed">
    <h1 style="padding: 30px 0;font-family: 'Yanone Kaffeesatz',Tahoma,sans-serif;text-transform: uppercase">
        Edit the primary colors of an image
    </h1>
</div>

<canvas id="canvasPanel" width="300" height="300" style="display: none;"></canvas>


<div class="category-products">
<ol class="products-list" id="products-list" style="padding: 20px 20px;">
<?php

/*
 * [2] => Default Category
    [3] => Stoffen
    [4] => Kralen
    [5] => Assortiment stoffen
    [6] => Knopen
    [7] => Band, lint & elastiek
    [8] => Assortiment kralen
    [9] => Onderdelen
    [10] => Draad
    [11] => Draad
    [12] => Gereedschap
    [13] => Juwelen
    [14] => Oorbellen
    [15] => Halskettingen
    [17] => Staff pick
    [18] => Strijkapplicaties
    [20] => Aanbevelingen
    [21] => Patronen
    [22] => Cadeaucheques
    [23] => Armbanden
    [24] => Ritsen
    [27] => Vilt
    [29] => Workshops
    [31] => Nice Price
    [33] => Staaltjes
    [34] => Afgewerkte stukken
    [36] => Naaibenodigdheden
    [39] => Ringen
    [40] => Mss Peacock
    [45] => Hangers en tussenstukken
    [47] => Veronique Van Asch
    [48] => Handwerk
    [49] => Soft Cactus vorige collecties
    [50] => Dwars
    [51] => Volumekorting
    [54] => Nice Price
    [55] => Eigen oorbellen
    [56] => Eigen halskettingen
    [57] => Eigen armbanden
    [58] => Eigen ringen
 *
 */

$collection = Mage::getModel('catalog/category')->load("3") /*8=assortiment kralen, 5=assortiment stoffen*/
    ->getProductCollection()
    ->addAttributeToSelect('*')
    ->addFieldToFilter('visibility', array(
        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
    )) //showing just products visible in catalog or both search and catalog
    ->addAttributeToSort('created_at', 'DESC')
    ->setPageSize(2500) // limit number of results returned
    ->setCurPage(0);

foreach ($collection as $product) {
    if (!strpos($product->getSku(), "sample") ) {
    ?>
    <li class="item">
        <div class="">

            <img class="img-to-scan"
                 id="img-<?php echo $product->getId(); ?>"
                 data-id="<?php echo $product->getId(); ?>"
                 src="<?php echo Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(252); ?>"
                 width="252" height="252" alt="<?php echo $product->getName(); ?>">
            <div class="colors" data-color-codes="<?php
                    $_resource = $product->getResource();
                    $colorCodes = $_resource->getAttributeRawValue($product->getId(), 'colors_hsl', 1);
                    echo $colorCodes;
            ?>">Loading ...</div>
            <div><?php echo $product->getName(); ?></div>

        </div>

    </li>

<?php
    }//if
}


?>
</ol>
</div>

</body>
</html>