img magic:
<?php


//exec('convert body.png -background black -alpha background 2body.png', $output,$exit_status);
//exec('convert fabric.jpg \
//\( 2body.png -blur "0x3" -auto-level \) -alpha set -virtual-pixel transparent \
//-compose displace -set option:compose:args "-20x-20" -composite 2displace_body.png', $output,$exit_status);
//exec('convert 2displace_body.png 2body.png -compose multiply -composite 2body_displace_composite.png', $output,$exit_status);


exec('convert body.png -background black -alpha background mainbody_clone2.png');
exec('convert -respect-parenthesis fabric.jpg mainbody_clone2.png \
\( -clone 1 -alpha off -blur 0x3 -alpha on -gravity north -crop 440x125+0+0 +repage \
-clone 1 -gravity south -crop 440x475+0+0 +repage -append -auto-level \) \
-delete 1 -alpha set -virtual-pixel transparent \
-compose displace -set option:compose:args "5x5" -composite 2displace_body.png');
exec('convert 2displace_body.png mainbody_clone.png -compose multiply -composite result2.png');



//if( $exit_status !== 0 ) {
//    // Error handle here
//    echo "<h1>error</h1>";
//    echo $exit_status;
//}

echo "<p>" . $output . "</p>";

?>

<img src="result2.png?<?php echo rand(); ?>" alt=""/>