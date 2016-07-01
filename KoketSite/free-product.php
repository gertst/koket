<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gert
 * Date: 26/08/14
 * Time: 10:54
 *
 * call this code at KoketSite/app/design/frontend/shopper/koket/template/checkout/cart.phtml like this:
 * <?php //GERT - get products for free when cart over XX EUR ?>
 * <?php include "free-product.php" ?>
 *
 */

$category_id_to_check = 30;//27;//category id of gift products to choose from
$priceThreshold = 35; //action visible when cart price (incl BTW) above or equals this value

$quote = Mage::getSingleton('checkout/session')->getQuote();
$items = $quote->getAllItems();
$priceInclVat = 0;
foreach ($items as $item) {
    $product = $item->getProduct();
    if (!$product->getData('special_price')) {
        $priceInclVat += $item->getRowTotalInclTax();
    }
}

$products = Mage::getModel('catalog/category')->load($category_id_to_check);
$productsList = $products->getProductCollection()->addAttributeToSelect('*');
$giftIds = array();

//push all gift products in array
foreach($productsList as $product)
{
    array_push($giftIds, $product->getSKU());
}
$product_already_in_cart = false;


//iterate products in cart and check if there's already a gift
$cart = Mage::getModel('checkout/cart')->getQuote();
foreach ($cart->getAllVisibleItems() as $cartitem) {
   //echo in_array($cartitem->getProduct()->getSKU(), $giftIds) . "id:" . $cartitem->getProduct()->getSKU();
   if (in_array($cartitem->getProduct()->getSKU(), $giftIds)) {
      $product_already_in_cart = true;
       break;
   }
}

if (!$product_already_in_cart) {

    $products = Mage::getModel('catalog/category')->load($category_id_to_check);
    $productsList = $products->getProductCollection()->addAttributeToSelect('*');

    // 1) TEASER
    if ($priceInclVat < $priceThreshold) {
        $priceMissing = $priceThreshold - $priceInclVat;
        ?>
        <div id="gifts">

            <div>
                <?php
                $free_product_header = Mage::getModel('cms/block')
                    ->setStoreId( Mage::app()->getStore()->getId() )
                    ->load('free-product-card-teaser');
                if($free_product_header->getIsActive()) {
                    echo str_replace("{price-missing}", $priceMissing, $this->getLayout()->createBlock('cms/block')->setBlockId('free-product-card-teaser')->toHtml());
                }
                ?>

            </div>

            <ul class="teaser-list">


                <?php

                foreach($productsList as $product)
                {
                    ?>
                    <li>
                        <?php $_store = $product->getStore(); ?>
                        <a href="<?php echo $product->getProductUrl() ?>" title="<?php echo $this->htmlEscape($product->getName()) ?>">
                            <img class="element" src="<?php echo $this->helper('catalog/image')->init($product, 'small_image')->resize(150, 150); ?>" alt="<?php echo $this->htmlEscape($product->getName()) ?>">
                        </a>
                    </li>
                <?php
                }
                ?>

            </ul>


        </div>
    <?php
    } else {
        // 2) BEDRAG HOOG GENOEG
    ?>
        <div id="gifts">

            <script>
                function alertBudgetTooLow(me) {
                    jQuery(me).contents().unwrap().wrap("").html("<div class='budget-too-low'>Je kunt je gratis paar oorbellen pas toevoegen wanneer je voor €&nbsp25 artikelen gekozen hebt.</div>");
                }
            </script>
            <div>
                <?php
                $free_product_header = Mage::getModel('cms/block')
                    ->setStoreId( Mage::app()->getStore()->getId() )
                    ->load('free-product-card-info');
                if($free_product_header->getIsActive()) {
                    echo $this->getLayout()->createBlock('cms/block')->setBlockId('free-product-card-info')->toHtml();
                }
                ?>

            </div>

            <div id="custom-caption" class="center"></div>
            <div class="center">

                <!-- prev/next links -->
                <a href="#" class="cycle-prev">&lt; Vorige</a> | <a href="#" class="cycle-next">Volgende &gt;</a>
            </div>

            <div class="cycle-slideshow"
                 data-cycle-fx="scrollHorz"
                 data-cycle-prev=".cycle-prev"
                 data-cycle-next=".cycle-next"
                 data-cycle-swipe=true
                 data-cycle-swipe-fx=scrollHorz
                 data-cycle-slides="div"
                 data-cycle-caption="#custom-caption"
                 data-cycle-caption-template="Geschenk {{slideNum}} van {{slideCount}}"
                 data-cycle-timeout="0">




                <?php


                foreach($productsList as $product)
                {
                    ?>
                    <div class="cycle-slide">
                        <?php $_store = $product->getStore(); ?>
                        <img class="element" src="<?php echo $this->helper('catalog/image')->init($product, 'small_image')->resize(370, 370); ?>" alt="<?php echo $this->htmlEscape($product->getName()) ?>"/>
                        <info class="element">
                            <p class="label"><a href="/<?php echo Mage::getResourceSingleton('catalog/product')->getAttributeRawValue($product->getId(), 'url_key', Mage::app()->getStore());?>"><?php echo $this->htmlEscape($product->getName()) ?></a></p>

                            <p class="regular-price">Normale prijs: <?php echo Mage::helper('core')->currency($product->getPrice());?></p>
                            <p class="special-price">Nu: <span>gratis</span></p>

                            <?php if ($priceInclVat >= $priceThreshold) {
                                if ($product->getStockItem()->getIsInStock()){ ?>
                                    <button class="button" onclick="window.location='<?php echo (string)Mage::helper('checkout/cart')->getAddUrl($product);?>'"><span><span>In Winkelmandje</span></span></button>
                                <?php } else { ?>
                                    <p class="out-of-stock">Niet meer in voorraad</p>
                                <?php }
                            } else {?>
                                <button class="button" onclick="alertBudgetTooLow(this)"><span><span>In Winkelmandje</span></span></button>
                            <?php } ?>
                        </info>

                    </div>
                <?php
                }
                ?>

            </div>

            <div class="center">

                <!-- prev/next links -->
                <a href="#" class="cycle-prev">&lt; Vorige</a> | <a href="#" class="cycle-next">Volgende &gt;</a>
            </div>

        </div>
    <?php
    }
    ?>


    <script src="http://malsup.github.com/jquery.cycle2.js"></script>

<?php
}
?>

