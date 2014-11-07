img magic:
<?php

//exec('convert test.jpg -resize 100x100 /var/importexport/testNew.jpg', $output,$exit_status);

exec('convert body.png -background black -alpha background 2body.png

convert fabric.png \
\( 2body.png -blur "0x3" -auto-level \) -alpha set -virtual-pixel transparent \
-compose displace -set option:compose:args "-20x-20" -composite 2displace_body.png

convert 2displace_body.png 2body.png -compose multiply -composite 2body_displace_composite.png');

//exec( "convert test.jpg -resize 200x200 output.jpg",$output,$exit_status );

if( $exit_status !== 0 ) {
    // Error handle here
    echo "<h1>error</h1>";
    echo $exit_status;
}

echo "<p>" . $output . "</p>";

?>

<img src="output.jpg?<?php echo rand(); ?>" alt=""/>