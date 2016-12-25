<?php

//http://stackoverflow.com/questions/7210620/magento-1-6-google-shopping-products-content/7549430#7549430

define('SAVE_FEED_LOCATION','google-shopping-products-list.txt');
set_time_limit(1800);
require_once dirname(__FILE__) . '/app/Mage.php';
Mage::app();
try{
    $handle = fopen(SAVE_FEED_LOCATION, 'w');

    $heading = array('id','mpn','title','description','link','image_link','price', 'sale_price', 'brand', 'product_type', 'condition', 'google_product_category', 'availability');
    $feed_line=implode("\t", $heading)."\r\n";
    fwrite($handle, $feed_line);


    //$excludeCatIds = array();//exclude 33 = staaltjes

    $products = Mage::getModel('catalog/product')->getCollection();
    //$products->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left');
    //$products->addAttributeToFilter('category_id', array('nin' => array('finset' => $excludeCatIds)));
    $products->addAttributeToFilter('status', 1);
    $products->addAttributeToFilter('visibility', 4);
    $products->addAttributeToSelect('*');

//    $products->joinField('qty',
//        'cataloginventory/stock_item',
//        'qty',
//        'product_id=entity_id',
//        '{{table}}.stock_id=1',
//        'left')
//        ->addAttributeToFilter('qty', array("gt" => 0));

    $prodIds=$products->getAllIds();

    $product = Mage::getModel('catalog/product');

    $counter_test = 0;
echo "<pre>";

    $category = Mage::getModel('catalog/category');
    $tree = $category->getTreeModel();
    $tree->load();
    $ids = $tree->getCollection()->getAllIds();
    $catNames = array();
    if ($ids){
        foreach ($ids as $id){
            $cat = Mage::getModel('catalog/category');
            $cat->load($id);
//            print_r($cat);
            if ($cat->getIsActive()) {
                $catNames[$cat->getId()] = $cat->getName();
                // echo($cat->getId() . " - " . $cat->getName() . $cat->getIsActive() . "<br>");
            }
        }
    }

    echo '<h2>' . date("Y-m-d H:i:s") . '</h2>';
    print_r($catNames);


    foreach($prodIds as $productId) {

        if (++$counter_test < 50000){


            $product->load($productId);

            $stock = $product->getStockItem();
            if (!$stock->getIsInStock()) {
                continue;
            }

            $categoryList = array();
            $_categories = $product->getCategoryCollection();

            foreach ($_categories as $_category){
                //print_r ($_category->getPathIds());
                //print_r($_category);
                $breadcrumb = createBreathCrumb($_category->getPathIds(), $catNames);
                if ($breadcrumb !== false) {
                    array_push($categoryList, $breadcrumb);
                }

            }

            $productTypeList = implode(", ", $categoryList);

            $product_data = array();

            $product_data['sku'] = $product->getSku();
            $product_data['mpn'] = $product->getSku();

            $title_temp = $product->getName();// . " - " . $productTypeList;
            $title_temp = $title_temp; // . " - " . "Stoffen, Kralen, Juwelen";
            $title_temp = str_replace("  ", " ", $title_temp);
//            $title_temp = str_replace(" > Assortiment kralen", "", $title_temp);
//            $title_temp = str_replace(" > Assortiment stoffen", "", $title_temp);
            $specialPrice = round($product->getSpecialPrice(),2);
            if ($specialPrice == 0) {
                $specialPrice = "";
            } else {
                $title_temp .= " - koopjes/solden/actie";
            }

            //no Maggy London
            $pos = strpos($title_temp, "Maggy London");
            if ($pos > 0) {
                continue;
            }

            $product_data['title'] = "UITVERKOOP! " . $title_temp;


            $product_data['description'] = substr(iconv("UTF-8","UTF-8//IGNORE",$product->getDescription()), 0, 900);
            $product_data['Deeplink'] = "http://www.kokettekatinka.be/".$product->getUrlPath();
            $product_data['image_link'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();

            $price_temp = round($product->getPrice(),2);
            $product_data['price'] = round($product->getPrice(),2);

            $product_data['sale_price'] = $specialPrice;



            $product_data['brand'] = $product->getAttributeText("brand");
            if ($product_data['brand'] == "andere") {
                $product_data['brand'] = "";
            }


            echo("<br/>".$counter_test . " " . $title_temp . ": " );



            $product_data['product_type'] = $productTypeList;
            $product_data['condition'] = "new";
            $product_data['category'] = "Arts & Entertainment > Hobbies & Creative Arts > Arts & Crafts > Art & Crafting Materials";
            //$product_data['manufacturer'] = $product->getAttribute("manufacturer");

            if (strrpos($productTypeList, "Juwelen") != false) {
                if (strrpos($productTypeList, "Oorbellen") != false) {
                    $product_data['category'] = "Apparel & Accessories > Jewelry > Earrings";
                } else if (strrpos($productTypeList, "Halskettingen") != false) {
                    $product_data['category'] = "Apparel & Accessories > Jewelry > Necklaces";
                } else if (strrpos($productTypeList, "Armbanden") != false) {
                    $product_data['category'] = "Apparel & Accessories > Jewelry > Bracelets";
                } else if (strrpos($productTypeList, "Ringen") != false) {
                    $product_data['category'] = "Apparel & Accessories > Jewelry > Rings";
                } else {
                    $product_data['category'] = "Apparel & Accessories > Jewelry";
                }
            }
            echo $productTypeList . " :: " . $product_data['category'] . "<hr/>";

            $product_data['availability'] = "in stock";

            foreach($product_data as $k=>$val){
                $bad=array('"',"\r\n","\n","\r","\t");
                $good=array(""," "," "," ","");
                $product_data[$k] = '"'.str_replace($bad,$good,$val).'"';
            }


            $feed_line = implode("\t", $product_data)."\r\n";
            fwrite($handle, $feed_line);
            fflush($handle);

        }

    }

    fclose($handle);
}
catch(Exception $e){
    die($e->getMessage());
}

function createBreathCrumb($ids,$catNames) {
    $list = array();
    $active = true;
    foreach ($ids as $catId) {
        if ($catId > 2) {
            //echo $catNames[$catId] ."---<br>";
            if ($catNames[$catId]) {
                array_push($list, $catNames[$catId]);
            } else {
                $active = false;
            }
        }
    }
    if ($active) {
        return implode(" > ", $list);
    } else {
        return false;
    }
}