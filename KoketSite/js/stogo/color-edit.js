function cssColorToHsl(cssColor) {
    var rgb = /rgb\((\d+), (\d+), (\d+)\)/.exec(cssColor);
    var hsl = rgbToHsl(rgb[1],rgb[2],rgb[3]);
    for (var i = 0; i < 3; i++) {
        hsl[i] = parseFloat(hsl[i]).toFixed(3);
    }
    return hsl;
}


$(window).on("load", function() {

    var $getColorButtons = $(".get-colors");


    var tmpCanvas = $('#canvasPanel')[0];

    var colorThief = new ColorThief();


    // Run Color Thief functions and display results below image.
    function showColorsForImage($img) {
        var id = $img.data("id");
        var nrOfSwatches = 7;
        var palette = colorThief.getPalette($img[0], nrOfSwatches);
        //console.log("palette: " + palette);
        var colorThiefOutput = {
            palette: palette,
            id: id
        };
        var $colorsNode = $img.next();
        var colorThiefOutputHTML = Mustache.to_html($('#color-thief-output-template').html(), colorThiefOutput);
        $colorsNode.html(colorThiefOutputHTML);
        var $swatches = $colorsNode.find(".swatch");

        var hslArray = [];
        //loop swatches and select stored ones
        $swatches.each(function( index ) {
            var hsl = cssColorToHsl($( this ).css("background-color"));
            if ($colorsNode.data("color-codes").indexOf(hsl.join("-")) > -1) {
                $(this).addClass("selected");
            }
            hslArray.push(hsl.join("-"));
        });
        //loop stored ones and add if not present yet
        var storedHslList = $colorsNode.data("color-codes").split(",");
        for (var i in storedHslList) {
            if (storedHslList[i] != "" && hslArray.indexOf(storedHslList[i]) == -1) {
                var hsl = storedHslList[i].split("-");
                var rgb = hslToRgb(parseFloat(hsl[0]),parseFloat(hsl[1]),parseFloat(hsl[2]));
                var swatchHtml = '<div class="swatch selected" style="background-color: rgb(' + rgb.join(",") + ')"></div>';
                $swatches.parent().append(swatchHtml);
                $swatches = $colorsNode.find(".swatch");
            }
        }

        $swatches.on('click', onSwatchClick);


        ///add extra swatch to pick color under image
        $colorsNode.find(".add-swatch").on('click', function(event) {
            event.preventDefault();
            var html = '<div class="swatch" style="background-color: #ff0000"></div>';
            $swatches.parent().append(html);
            $colorsNode.find(".swatch").last().on('click', onSwatchClick);
            tmpCanvas.width = $img.width();
            tmpCanvas.height = $img.height();
            tmpCanvas.getContext('2d').drawImage($img[0], 0, 0);

            $img.css("cursor", "crosshair");
            $img.mousemove(function(event){
                var pixelData = tmpCanvas.getContext('2d').getImageData(event.offsetX, event.offsetY, 1, 1).data;
                $colorsNode.find(".swatch").last().css("background-color", "rgb(" + pixelData[0] + ", " + pixelData[1] + ", " + pixelData[2] + ")");

                //console.log(pixelData);
            });
            $img.click(function(event) {
                $img.off("mousemove");
                $img.css("cursor", "default");

            });
        });

        function onSwatchClick(event) {
            var id = $(event.currentTarget).parent().data("id");
            $colorsNode.fadeTo(200, .5);
            $(event.currentTarget).toggleClass("selected");
            $swatches = $colorsNode.find(".swatch");
            var colorList = [];
            var hueList = [null,null,null,null,null];

            var $i = 0;
            $swatches.filter(".selected").each(function(){
                var cssColor = $(this).css("background-color");
                var hsl = cssColorToHsl(cssColor)
                colorList.push(hsl.join("-"));
                hueList[$i] = hsl[0];
                $i ++;
            });
            console.log("colorList: " + colorList);



            $.ajax({
                url:"http://www.kokettekatinka.be/color-edit-save.php?id="+ id + "&colors=" + colorList.join(",") +"&hueList=" + hueList.join(","),
                cache:false
            })
                .done(function( data ) {
                    console.log("saved: " + data);
                    $colorsNode.fadeTo(200, 1);
                });
            //console.log("tmp" + id);
        }

    };



    console.log("img-to-scan: " + $(".img-to-scan").length);

    $(".img-to-scan").each(function( index ) {
        showColorsForImage($(this));
    });
    //alert('Ready.');



});