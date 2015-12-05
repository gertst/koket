<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gert
 * Date: 26/08/14
 * Time: 10:54
 *
 * call this code at KoketSite/app/design/frontend/shopper/koket/template/checkout/cart.phtml like this:
 * <?php //GERT - get products for free when cart over 25 EUR ?>
 * <?php include "free-product.php" ?>
 *
 */

$category_id_to_check = 30;//27;//category id of gift products to choose from
$priceThreshold = 25; //action visible when cart price (incl BTW) above or equals this value

$quote = Mage::getSingleton('checkout/session')->getQuote();
$items = $quote->getAllItems();
$priceInclVat = 0;
foreach ($items as $item) {
    $priceInclVat += $item->getRowTotalInclTax();
}


$products = Mage::getModel('catalog/category')->load($category_id_to_check);
$productslist = $products->getProductCollection()->addAttributeToSelect('*');
$giftIds = array();

//push all gift products in array
foreach($productslist as $product)
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
    $productslist = $products->getProductCollection()->addAttributeToSelect('*');
    /*<button type="button" title="Add Loan Phone" class="button" onclick="setLocation('<?php echo (string)Mage::helper('checkout/cart')->getAddUrl($_product);?>')"><span class="green">Add a Loan Phone (+£10)</span></button>*/
    ?>
    <div id="gifts">

        <script>
        function alertBudgetTooLow(me) {
            jQuery(me).contents().unwrap().wrap("").html("<div class='budget-too-low'>Je kunt je gratis paar oorbellen pas toevoegen wanneer je voor €&nbsp25 artikelen gekozen hebt.</div>");
        }
        </script>
        <div>
            <h2>Je uniek cadeau voor de Dag van de Webshop</h2>
            <div class="info">
                <p>Op 5, 6 en 7 november is het weer Dag van de Webshop. De perfecte gelegenheid om onze webklanten in de bloemetjes te zetten.</p>
                <p>Om jou als klant te bedanken, mag je tot en met <strong>zaterdag 7 november</strong>, een <strong>gratis paar oorbellen met een winkelwaarde van 15 euro</strong> uitkiezen bij elke online aankoop <strong>vanaf 25 euro</strong>!</p>
            </div>
            <div class="kies">Kies uit deze acht kleuren:</div>
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
            data-cycle-slides="div"
            data-cycle-caption="#custom-caption"
            data-cycle-caption-template="Product {{slideNum}} van {{slideCount}}"
            data-cycle-timeout="0">



        <?php
        foreach($productslist as $product)
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
    <script src="http://malsup.github.com/jquery.cycle2.js"></script>

<?php
}
?>

