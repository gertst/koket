<?php
if ($_GET['randomId'] != "aBt6iZP1dLOkaI_uL4eDUo1nnWK0uASifQ1IqKI2lHge0JHaytKduK2iO92mnarf") {
    echo "Access Denied";
    exit();
}

// display the HTML code:
echo stripslashes($_POST['wproPreviewHTML']);

?>  
