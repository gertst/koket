<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gert
 * Date: 26/08/14
 * Time: 10:54
 */

$category_id_to_check = 30;//30;//27;//category id of gift products to choose from
$priceThreshold = 25; //action visible when cart price (incl BTW) above or equals this value

$quote = Mage::getSingleton('checkout/session')->getQuote();
$items = $quote->getAllItems();
$priceInclVat = 0;
foreach ($items as $item) {
    $priceInclVat += $item->getRowTotalInclTax();
}
if ($priceInclVat >= $priceThreshold) {

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

            <h2>Kies je geschenkje!</h2>
            <p>Bij bestellingen van € 25.00 of meer krijg je van ons een extraatje! Deze actie loopt nog gedurende de hele maand september.</p>
            <p>Kies hieronder een van de gratis producten:</p>

            <ul class="clearfix">

            <?php
            foreach($productslist as $product)
            {
            ?>
                <li>
                    <img src="<?php echo $this->helper('catalog/image')->init($product, 'small_image')->resize(170, 170); ?>" alt="<?php echo $this->htmlEscape($product->getName()) ?>"/>
                    <p class="label"><?php echo $this->htmlEscape($product->getName()) ?></p>
                    <button class="button" onclick="window.location='<?php echo (string)Mage::helper('checkout/cart')->getAddUrl($product);?>'"><span><span>Toevoegen</span></span></button>

                </li>
            <?php
            }
            ?>
            </ul>
            <p></p>
        </div>
<?php
    }
} else {
?>
    <div id="gifts">
        <h2>Koop voor minstens € 25.00 en je krijgt van ons een geschenkje!</h2>
        <p>Deze actie loopt nog gedurende de hele maand september.</p>
    </div>
<?php
}
?>

