<?php
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app();

?>
<table>
    <tr >
        <td>name</td>
        <td>sku</td>
        <td>category</td>
        <td>manufacturer price</td>
        <td>price (excl btw)</td>
        <td>qty ordered</td>
        <td>stock</td>
        <td>profit per unit</td>
        <td>total cost</td>
        <td>total profit</td>
    </tr>

    <?php
    $fromDate = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2014));
    $toDate = date("Y-m-d H:i:s", time());

    $collection = Mage::getModel('catalog/product')->getCollection();
    $collection->addAttributeToSelect('name');
    $collection->addAttributeToSelect('sku');
    $collection->addAttributeToSelect('price');
    $collection->addAttributeToSelect('manufacturer_price');
    $collection->addFieldToFilter(array(
        array('attribute'=>'manufacturer_price','gteq'=>'0'),
        //array('attribute'=>'sku','eq'=>'JO-00004'),
    ));
    $i = 0;



    foreach ( $collection as $prod ) :
        $_product = Mage::getModel('catalog/product');
        $_product->load($prod['entity_id']);
        $i++;
        $stock = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();

//        $query = Mage::getResourceModel('sales/order_item_collection');
//        $query->getSelect()->reset(Zend_Db_Select::COLUMNS)
//            ->columns(array('sku','SUM(row_total) as summed_qty_ordered'))
//            ->where(new Zend_Db_Expr('sku = "' . $_product->sku . '"'))
//            ->group(array('sku'));

        $sum = new Zend_Db_Expr(sprintf('SUM(%s)', 'qty_ordered'));
        $query = Mage::getResourceModel('sales/order_item_collection')
            ->addFieldToSelect('sku')
            ->addExpressionFieldToSelect('summed_qty_ordered', $sum, 'qty_ordered')
            ->addAttributeToFilter('sku', array('eq' => $_product->sku));

        $orderData = $query->getData();
        $ordered_qty = $orderData[0]["summed_qty_ordered"];


        $priceExclTax = $_product->price / 1.21;
        $profitPerUnit = $priceExclTax - $_product->manufacturer_price;
        $costTotal = ($ordered_qty + $stock ) * $_product->manufacturer_price;
        $profitTotal = ($ordered_qty * $priceExclTax) - $costTotal;

        ?>
        <tr >
            <td><?php echo $_product->name ?></td>
            <td><?php echo $_product->sku ?></td>
            <td><?php echo substr($_product->sku, 0, 2) ?></td>
            <td><?php echo $_product->manufacturer_price; ?></td>
            <td><?php echo $priceExclTax ?></td>
            <td><?php echo $ordered_qty ?></td>
            <td><?php echo $stock ?></td>
            <td><?php echo $profitPerUnit ?></td>
            <td><?php echo $costTotal ?></td>
            <td><?php echo $profitTotal ?></td>
        </tr>
    <?php
    endforeach;
    ?>
    </table>
