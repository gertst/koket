<?php

$store_id = 1;
$csv_filepath = "subscribers.csv";
//ID,E-mail,Type,"Customer First Name","Customer Last Name",Status,Website,Winkel,Winkelzicht
$email_csv_column_index = 1;
$csv_delimiter = ',';
$csv_enclosure = '"';

require "../app/Mage.php";
Mage::app()->setCurrentStore($store_id);

$fp = fopen($csv_filepath, "r");
if (!$fp) die("{$csv_filepath} not found\n");
while (($row = fgetcsv($fp, 0, $csv_delimiter, $csv_enclosure)) !== false) {
    $email = trim($row[$email_csv_column_index]);
    if (strlen($email) == 0) continue;
    echo "$email ";

    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
    if ($subscriber->getId()) {
        echo " already subscribed<br/>";
        continue;
    }

    Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
    echo ": ok<br/>";
}

echo "<br/>Import finished";