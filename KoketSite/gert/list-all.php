<?php
function getcustomers() {
    /* Magento's Mage.php path
     * Mage Enabler users may skip these lines
     */
    require_once ("../magento/app/Mage.php");
    umask(0);
    Mage::app("default");
    /* Magento's Mage.php path */

    /* Get customer model, run a query */
    $collection = Mage::getModel('customer/customer')
        ->getCollection()
        ->addAttributeToSelect('*');

    $result = array();
    foreach ($collection as $customer) {
        $result[] = $customer->toArray();
    }

    return $result;
}
?>
<html>
<head>
    <title>Customers</title>
    <style>
        table {
            border-collapse: collapse;
        }
        td {
            padding: 5px;
            border: 1px solid #000000;
        }
    </style>
</head>
<body>
<table>
    <tr>
        <td>ID</td>
        <td>Lastname</td>
        <td>Firstname</td>
        <td>Email</td>
        <td>Is Active?</td>
        <td>Date Created</td>
        <td>Date Updated</td>
    </tr>
    <?php
    $result = getcustomers();
    if(count($result) > 0){
        foreach($result as $key => $value){
            echo "<tr>";
            echo "<td>".$value['entity_id']."</td>";
            echo "<td>".$value['lastname']."</td>";
            echo "<td>".$value['firstname']."</td>";
            echo "<td>".$value['email']."</td>";
            echo "<td>";
            echo $value['is_active'] == 1 ? "Yes" : "No";
            echo "</td>";
            echo "<td>".$value['created_at']."</td>";
            echo "<td>".$value['updated_at']."</td>";
            echo "</tr>";
        }
    }else{
        echo "<tr><td colspan=\"7\">No records found</td></tr>";
    }
    ?>
</table>
</body>
</html>