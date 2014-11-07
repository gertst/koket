<?php
/**
 * WJB - Order Samples in Magento 1.8
 *
 * https://gist.github.com/WazzaJB/8083094
 *
 * used to create free samples of products (GERT: gebruikt bij stoffen)
 *
 * Upload this file to the root of your Magento installation and then you will
 * need to add the following snippet to your 'template/catalog/product/view.phtml'
 *
 * You may need to change some of the values below in the sample creation code
 * but this is all commented so it should be simple enough - my advice would be
 * to read through and edit where necessary

<input type="hidden" name="sample-sku" value="<?php echo $this->htmlEscape($_product->getSku()) ?>" /> 
<button type="button"
onclick="sampleAddToCart(this)"
class="lwd-button lwd-button-sample">
<?php echo $this->__('Order Sample'); ?>
</button>
<script type="text/javascript">
function sampleAddToCart(sample)
{
var do_continue = true;

var sku = jQuery(sample).data('sku');

jQuery('.required-entry[name^="super_attribute"]').each( function() {
if( !jQuery(this).val() )
{
jQuery(this).addClass('validation-failed');
jQuery(this).after('<div class="validation-advice" id="advice-required-entry-attribute">This is a required field.</div>');
do_continue = false;
}
});

if( do_continue )
{
console.log('lets go');
jQuery('#product_addtocart_form')
.attr('action', '/orderSample.php' )
.submit();
}

return false;
}
</script>

 */

///////////////////////////////////
// STORE GIVEN SKU IN A VARIABLE //
///////////////////////////////////
$sku = $_POST['sample-sku'];

//////////////////////////////////////////////
// IF NO SKU IS FOUND THEN REDIRECT US HOME //
//////////////////////////////////////////////
if (!$sku) {
    header("Location: ".Mage::getStoreConfig('web/unsecure/base_url'));
    exit;
}


//////////////////////////////////////////////////////
// REQUIRE MAGE APP FOR ACCESSING MAGENTO FUNCTIONS //
//////////////////////////////////////////////////////
require_once 'app/Mage.php';
umask(0);
Mage::app(0);


///////////////////////////////////////////////////////////////////////
// LOAD USERS CURRENT SESSION - THIS FIXES THE SAMPLE SYSTEM FOR 1.8 //
///////////////////////////////////////////////////////////////////////
Mage::getSingleton('core/session', array('name' => 'frontend'));
$session = Mage::getSingleton('customer/session');


////////////////////////////////////////
// GET THE FULL PRODUCT FROM IT'S SKU //
////////////////////////////////////////
$prod = Mage::getModel('catalog/product');
$p    = $prod->loadByAttribute('sku', $sku);


///////////////////////////////////////////////////////////
// LOADS THE CORRECT VARIANT IF A SUPER ATTRIBUTE IS SET //
///////////////////////////////////////////////////////////
if( isset($_POST['super_attribute']) )
{
    $the_product = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($_POST['super_attribute'], $p);
    if ( is_object($the_product) )
    {
        $p = $the_product;
        $sku = $p->getSku();
    }
}

$origProduct = $p;

//////////////////////////////////////////////////
// IF THE PRODUCT IS NOT AN OBJECT THEN GO HOME //
//////////////////////////////////////////////////
if ( !is_object($p) ) {
    header("Location: ".Mage::getStoreConfig('web/unsecure/base_url'));
    exit;
}


/////////////////////////////////////////////////////////////////////
// STORE ORIGINAL PRODUCTS NAME IN CASE WE NEED TO CREATE A SAMPLE //
/////////////////////////////////////////////////////////////////////
$name = $p->getName();


////////////////////////////////////
// SEE IF A SAMPLE ALREADY EXISTS //
////////////////////////////////////
$p = $prod->loadByAttribute('sku', $sku . "-sample");

if ( is_object($p) ){
    $pId = $p->getId(); }
else {
    $pId = false;
}

if ( !$pId )
{

    $product = $prod;
    $product->setSku($sku . "-sample"); // PREPEND 'S-' TO SKU TO MAKE IT CLEAR THIS IS A SAMPLE
    $product->setName("Staaltje - " . $name); // SET THE NAME
    $body = "De kost van het staaltje (€0,30) krijg je volledig terugbetaald in de vorm van kortingspunten.<br/>Deze worden automatisch verrekend bij je volgende aankoop, zowel in de webshop als in de winkel.<br/>Met kortingspunten kan je geen staaltjes kopen.";
    $product->setDescription($body); // SET THE DESCRIPTION
    $product->setShortDescription($body);
    $product->setPrice(0.30); // SET THE PRICE TO 3.00 - YOU MAY WANT TO CHANGE THIS
    $product->setTypeId('simple'); // WE WANT THIS TO BE A SIMPLE PRODUCT

    $product->setAttributeSetId(10); // THIS NEEDS CHANGING - IN MY CASE IT WAS 4 - stoffen

    $product->setCategoryIds("33"); // CHECK YOUR VALUE(s) staaltjes: 28
    $product->setWeight(4); // SET THE SAMPLES WEIGHT HERE IF YOU HAVE WEIGHT BASED SHIPPING
    $product->setTaxClassId(5); // SET THE TAX GROUP: hoog
    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);
    $product->setStatus(1); // ENABLE THE PRODUCT

    //IMAGES
    $product->setImageUrl($origProduct->getImageUrl()); // set the images from the original product
    $product->setThumbnailUrl($origProduct->getImageUrl());
    $product->setImage($origProduct->getSmallImage());
    $product->setMediaGallery (array("images"=>array (), "values"=>array ()));
    $product->addImageToMediaGallery($_SERVER['DOCUMENT_ROOT']."/media/catalog/product".$origProduct->getSmallImage(), array("image","small_image","thumbnail"), false, false);

    // ASSIGN PRODUCT TO THE DEFAULT WEBSITE
    $product->setWebsiteIds(array(
        Mage::app()->getStore(true)->getWebsite()->getId()
    ));

    // GET THE STOCK INFORMATION
    $stockData = $product->getStockData();

    // UPDATE STOCK DATA USING NEW DATA
    $stockData['qty']          = 1; // SET STOCK TO 1
    $stockData['is_in_stock']  = 1; // SET PRODUCT TO IN STOCK
    $stockData['manage_stock'] = 0; // DON'T MANAGE THE STOCK OF SAMPLES
    $stockData['max_sale_qty'] = 1; // ALLOW FOR ONLY ONE OF EACH SAMPLE IN CART

    // SET THE STOCK INFORMATION
    $product->setStockData($stockData);

    // SAVE/CREATE THE PRODUCT
    $product->save();

    // GET THE PRODUCT ID
    $pId = $product->getId();
}

//////////////////////////////
// LOAD THE PRODUCT FROM ID //
//////////////////////////////
$product = Mage::getModel('catalog/product')
    ->setStoreId(Mage::app()->getStore()->getId())
    ->load($pId);


////////////////////////////////////////////////////////////////////
// REDIRECT THE USER TO THE CART WITH THE CORRECTLY GENERATED URL //
////////////////////////////////////////////////////////////////////
$formKey = Mage::getSingleton('core/session')->getFormKey();
header("Location: /checkout/cart/add/product/" . $product->getId() . "/form_key/" . $formKey . "/");
exit;
?>
