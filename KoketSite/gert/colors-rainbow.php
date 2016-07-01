<?php

$mageFilename = '../app/Mage.php';
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


<html>
<head>
    <title>Test color sorting and distance</title>
    <style>
        .color-div {
            display: block;
            position: absolute;
            top:30px;
            padding: 0px;
            margin: 0px;
        }

        .products {
            position: absolute;
            top: 200px;
        }
    </style>
    <script src="/js/queldorei/jquery-1.8.2.min.js"></script>
</head>
<body>
    <script>
        var colors = [];

        $(document).ready(function() {


            $(".img-to-scan").each(function( index ) {
                var id = $(this).data("id");
                var $colorsNode = $(this).next();
                var codes = $colorsNode.data("color-codes");

                var numberPattern = /\d+/g;

                //get all numbers out of the code string
                var values = codes.match( numberPattern );
                if (values) {
                    for (var i=0; i < values.length/3; i+=3){
                        colors.push (
                            {
                                r: parseInt(values[i+0]),
                                g: parseInt(values[i+1]),
                                b: parseInt(values[i+2]),
                                id:id
                            });
                    }
                }
            });



            //colors = sortByType(colors, "sat");
            colors = sortByType(colors, "val");
            colors = sortByType(colors, "hue");
            showColorbar();

            });

        /**
         *
         * @param colors
         * @param type can be "hue", "sat" or "val"
         * @returns {*}
         */
        function sortByType(colors, type) {
            for (var c = 0; c < colors.length; c++) {
                /* Get the hex value without hash symbol. */
                /* Get the RGB values to calculate the Hue. */
                var r = colors[c].r;
                var g = colors[c].g;
                var b = colors[c].b;

                /* Getting the Max and Min values for Chroma. */
                var max = Math.max.apply(Math, [r,g,b]);
                var min = Math.min.apply(Math, [r,g,b]);

                /* Variables for HSV value of hex color. */
                var chr = max-min;
                var hue = 0;
                var val = max;
                var sat = 0;

                if (val > 0) {
                    /* Calculate Saturation only if Value isn't 0. */
                    sat = chr/val;
                    if (sat > 0) {
                        if (r == max) {
                            hue = 60*(((g-min)-(b-min))/chr);
                            if (hue < 0) {hue += 360;}
                        } else if (g == max) {
                            hue = 120+60*(((b-min)-(r-min))/chr);
                        } else if (b == max) {
                            hue = 240+60*(((r-min)-(g-min))/chr);
                        }
                    }
                }

                /* Modifies existing objects by adding HSV values. */
                colors[c].hue = hue;
                colors[c].sat = sat;
                colors[c].val = val;
            }

            /* Sort by Hue. */
            return colors.sort(function(a,b){return a[type] - b[type];});
        }

        function showColorbar() {
            var html = "";
            var colorDivWidth = ($("body").width() / colors.length);
            var j = 0;
            for (var i=0; i < colors.length; i++){
                if (colors[i].sat > 0.1) {
                    html = html + "<div class='color-div' data-id='" + colors[i].id + "' data-hue='" + colors[i].hue + "' style='left:" + (colorDivWidth *j) + "px; width:" + colorDivWidth + "px; height:" + (30 + colors[i].sat * 100) +  "px; background-color: rgb(" + colors[i].r + "," + colors[i].g + "," + colors[i].b + ");'></div>"
                    j++;
                }
            }
            $(".color-bar").html(html);

            $(".color-div").hover(function(){
                $("#img-" + $(this).data("id")).parent().show();
                $(".info").text("hue: " + $(this).data("hue"));
            }, function(){
                $("#img-" + $(this).data("id")).parent().hide();
            });
        }





    </script>

    <div class="color-bar"></div>

    <div class="info"></div>

    <div class="products">
        <?php

        //make sure to have attribute 'colors_rgb' as Global and with "showColorbar in product list" to Yes! Rebuild index!
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(array('name', 'id', 'product_url', 'thumbnail', 'colors_rgb'))
            ->addAttributeToFilter('colors_rgb', array('like' => 'rgb%'))
            ->load();

        echo "LENGTH: " . count($collection) . " -- ";

        foreach ($collection as $product) {
            $colorCodes = $product->getData("colors_rgb");

            ?>
            <div class="item" style="display: none">

                <img class="img-to-scan "

                     id="img-<?php echo $product->getId(); ?>"
                     data-id="<?php echo $product->getId(); ?>"
                     src="<?php echo Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(252); ?>"
                     width="252" height="252" alt="<?php echo $product->getName(); ?>">
                <div class="colors" data-color-codes="<?php echo $colorCodes; ?>"><?php echo $colorCodes; ?></div>

            </div>

            <?php
            }
            ?>
    </div>


</body>
</html>