$(document).ready(function () {

    var $getColorButtons = $(".get-colors");


    var colorThief = new ColorThief();


    // Run Color Thief functions and display results below image.
    function showColorsForImage($this) {
        var id = $this.data("id");
        //var image                    = $("#img-" + id)[0];
        //var color                    = colorThief.getColor(image);
        var palette                  = colorThief.getPalette($this[0], 5);

        var colorThiefOutput = {
            palette: palette,
            id: id
        };
        var $colorsNode = $this.next();
        var colorThiefOutputHTML = Mustache.to_html($('#color-thief-output-template').html(), colorThiefOutput);
        $colorsNode.html(colorThiefOutputHTML);
        var $swatches = $colorsNode.find(".swatch");

        //loop swatches and select stored ones
        $swatches.each(function( index ) {
            if ($colorsNode.data("color-codes").indexOf($( this ).css("background-color")) > -1) {
                $(this).addClass("selected");
            }
        });

        $swatches.on('click', function(event) {
            var id = $(this).parent().data("id");
            $colorsNode.fadeTo(200, .5);
            $(this).toggleClass("selected");

            var colorList = $swatches.filter(".selected").map(function(){return $(this).css("background-color");}).get();


            $.ajax({
                url:"http://www.kokettekatinka.local/color-edit-save.php?id="+ id + "&colors=" + colorList,
                cache:false
            })
                .done(function( data ) {
                    console.log("saved: " + data);
                    $colorsNode.fadeTo(200, 1);
                });
            //console.log("tmp" + id);
        });

    };



    $(".img-to-scan").each(function( index ) {
        showColorsForImage($(this));
    });




});