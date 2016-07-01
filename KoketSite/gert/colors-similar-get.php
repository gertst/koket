<div class="holder">

    <h3>Produkten in bijpassende kleuren</h3>

    <?php


    $hue = floatval($_GET["hue"]); //between 0 and 1
    $r = $_GET["r"];
    $g = $_GET["g"];
    $b = $_GET["b"];
    $range = 0.020;


    $mageFilename = '../app/Mage.php';
    require_once $mageFilename;
    Mage::setIsDeveloperMode(true);
    ini_set('display_errors', 1);
    umask(0);
    Mage::app();

    function createWhere ($hue, $range, $fieldName) {
        if ($hue - $range < 0) {
            $tooSmall = " OR (" . $fieldName . " > " . (1 - $hue - $range) . ") ";
        } else {
            $tooSmall = "";
        }

        if ($hue + $range > 1) {
            $tooLarge = " OR (" . $fieldName . " < " . ($hue + $range - 1) . ") ";
        } else {
            $tooLarge = "";
        }
        $query = "(" . $fieldName . " > " . ($hue - $range) . " AND " . $fieldName . " < " . ($hue + $range) . ") " . $tooSmall . " " . $tooLarge ;
        return $query;
    }

    function getImageUrl($id) {
        $product = Mage::getModel('catalog/product')->load($id);
        return Mage::helper('catalog/image')->init($product, 'image')->resize(200,200);
        //return $products->getSmallImage();
    }

    function getStyle($product, $index, $r,$g,$b) {
        $swatches = explode(",", $product["colors_hsl"]);
        $hsl = explode("-", $swatches[$index]);
        $contrast = $hsl[2] < 0.5 ? "#FFFFFF" : "#000000";
        return "background-color:hsl(" . $hsl[0]*360 . "," . $hsl[1]*100 . "%," . $hsl[2]*100 ."%); color:" . $contrast;
        //return "background-color: rgb($r,$g,$b); color:$contrast";
    }




    function getSwatches($product) {
        $html = "<div class='swatches'>";
        $swatches = explode(",", $product["colors_hsl"]);
        foreach ($swatches as $hslString) {
            $hsl = explode("-", $hslString);
            $html .= "<div class='swatch' style='background-color:hsl(" . $hsl[0]*360 . "," . $hsl[1]*100 . "%," . $hsl[2]*100 ."%)'></div>";
        }
        $html .= "</div>";
        return $html;
    }


    $query = "SELECT DISTINCT `entity_id`, `name`, `price`, `sku`, `small_image`, `special_price`, `special_to_date`, `thumbnail`, `url_path`, `visibility`, `colors_hsl`, `color_hue1`, `color_hue2`, `color_hue3`, `color_hue4`, `color_hue5` FROM mag_catalog_product_flat_1 ";





    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');


    $query .= "WHERE ";
    $query .= createWhere($hue, $range, "color_hue1");
    $query .= " OR ";
    $query .= createWhere($hue, $range, "color_hue2");
    $query .= " OR ";
    $query .= createWhere($hue, $range, "color_hue3");
    $query .= " OR ";
    $query .= createWhere($hue, $range, "color_hue4");
    $query .= " OR ";
    $query .= createWhere($hue, $range, "color_hue5");

    $query .= " AND visibility <> 1 ";


    $query .= "ORDER BY color_hue1";

    $results = $readConnection->fetchAll($query);


    //echo var_dump($results);


    //return;

    //Array ( [name] => Oorbellen Pink Tears [price] => 30.0000 [short_description] => Oorbellen met kralen van roze chalcedoon en 14kt vergulde oorhaakjes. [sku] => JO-00029 [small_image] => /j/o/jo-00029.jpg [special_price] => [special_to_date] => [thumbnail] => /j/o/jo-00029.jpg [url_path] => oorbellen-pink-tears [visibility] => 4 [colors_rgb] => [color_hue1] => 0.016 [color_hue2] => [color_hue3] => [color_hue4] => [color_hue5] => )


    foreach ($results as $product) {

        ?>
        <div class="box effect-duke">
            <a href="/<? echo $product["url_path"]; ?>">
                <div class="img-holder">
                    <img src="<? echo getImageUrl($product["entity_id"]);?>" alt="<? echo $product["name"]; ?>">
                </div>
                <h4 style="<? echo getStyle($product, 0, $r, $g, $b) ?>">
                    <? echo $product["name"]; ?>
                </h4>
            </a>


            <? //echo getSwatches($product)?>
        </div>


    <?

    }

    ?>


</div>
