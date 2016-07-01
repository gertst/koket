<html>
<head>
    <meta http-equiv="X-UA-Compatible">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">

    <title>Color Similar</title>
    <link href='https://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:300,400,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Alegreya:300,400,700' rel='stylesheet' type='text/css'>
    <script src="/js/stogo/colours.js"></script>
    <style>

        .swatches {
            /*display: flex;*/
            display: none;
        }
        .swatch{
            width:25px;
            height: 25px;
            background-color: cyan;
        }

        .picker {
            width: 100%;
        }

        #canvasPanel {
            display: none;
            width: 100%;
        }

        .similar {
            overflow: hidden;
            margin: 0px;
            padding: 10px;
        }

        .similar .box {
            float: left;
            position: relative;
            width: 20%;
            padding-bottom: 20%;
        }


        @media only screen and (max-width : 480px) {
            /* Smartphone view: 1 tile */
            .similar .box {
                width: 100%;
                padding-bottom: 100%;
            }
        }
        @media only screen and (max-width : 650px) and (min-width : 481px) {
            /* Tablet view: 2 tiles */
            .similar .box {
                width: 50%;
                padding-bottom: 50%;
            }
        }
        @media only screen and (max-width : 1050px) and (min-width : 651px) {
            /* Small desktop / ipad view: 3 tiles */
            .similar .box {
                width: 33.3%;
                padding-bottom: 33.3%;
            }
        }
        @media only screen and (max-width : 1290px) and (min-width : 1051px) {
            /* Medium desktop: 4 tiles */
            .similar .box {
                width: 25%;
                padding-bottom: 25%;
            }
        }

        .similar .box img {
            width: 100%;
        }

        .similar .img-holder {
            position: absolute;
            width: 98%;
            overflow: hidden;
            padding: 0;
            margin: 1%;
        }


        div.effect-duke h4 {
            position: absolute;
            bottom: 0px;
            left: 0;
            margin: 1%;
            padding: 10px 0;
            width: 98%;
            border: 0px;
            text-transform: none;
            font-size: 90%;
            opacity: 0;
            -webkit-transform: scale3d(0.8,0.8,1);
            transform: scale3d(0.8,0.8,1);
            -webkit-transform-origin: 50% -100%;
            background-color: rgba(0,0,0,0.17);
            font-family: sans-serif;
            font-weight: lighter;
            text-align: center;
        }

        div.effect-duke:hover h4 {
            opacity: 1;
            -webkit-transform: scale3d(1,1,1);
            transform: scale3d(1,1,1);
        }

        div.effect-duke img {
            -webkit-transition: opacity 0.35s, -webkit-transform 0.35s;
            transition: opacity 1s, transform 0.35s;
        }


        div.effect-duke h4 {
            -webkit-transition: opacity 0.35s, -webkit-transform 0.35s;
            transition: opacity 0.35s, transform 0.35s;
        }

        div.effect-duke:hover img {
            -webkit-transform: scale3d(1.5,1.5,1.5);
            transform: scale3d(1.5,1.5,1.5);
        }


    </style>
</head>
<body>
    <script src="/js/queldorei/jquery-1.8.2.min.js"></script>

    <image class="picker" src="colorpicker.png"></image>


    <canvas id="canvasPanel" ></canvas>

    <div class="similar"></div>

    <script>

        var scaleX,scaleY;
        var tmpCanvas;
        var $img;

        function getHue(pixelData){
            return rgbToHsl(pixelData[0], pixelData[1], pixelData[2])[0];
        }

        $(window).resize(function(){
            scaleX = tmpCanvas.width/$img.width();
            scaleY = tmpCanvas.height/$img.height();
        });

        $(window).on("load", function() {
            tmpCanvas = $('#canvasPanel')[0];
            $img = $(".picker");
            tmpCanvas.width = 2500;
            tmpCanvas.height = 134;
            scaleX = tmpCanvas.width/$img.width();
            scaleY = tmpCanvas.height/$img.height();
            console.log($img.width(), tmpCanvas.width);

            tmpCanvas.getContext('2d').drawImage($img[0], 0, 0);
            $img.css("cursor", "crosshair");

            $img.mousedown(function(event) {

                var pixelData = tmpCanvas.getContext('2d').getImageData(event.offsetX * scaleX, event.offsetY * scaleY, 1, 1).data;
                console.log(event.offsetX, $img.width(), scaleX);
                $(".similar").css("background-color", "rgb(" + pixelData[0] + ", " + pixelData[1] + ", " + pixelData[2] + ")");
                $(".similar").load("/gert/colors-similar-get.php?r=" + pixelData[0] + "&g=" + pixelData[1] + "&b=" + pixelData[2] + "&hue=" + getHue(pixelData));

            });
        });
    </script>

</body>

</html>