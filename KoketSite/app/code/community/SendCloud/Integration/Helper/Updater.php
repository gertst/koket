<?php
/**
 * Comments are left in for debugging purposes. The main problem being
 * that Magento, PHP or MySQL has a query cache issues.
 */
class SendCloud_Integration_Helper_Updater extends Mage_Core_Helper_Data
{
    public function check($order) {
        $id = (int)$order->getId();

        $cache = Mage::app()->getCache();
        if($cache->load("sendcloud_order_".$id)) {
            $parcelid = $cache->load("sendcloud_order_".$id);
//            echo date('H:i:s').' [CHECK IN DB] Result is '.$parcelid ."\n";
            return $parcelid;
        }
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('sales/order');
        $writeConnection = $resource->getConnection('core_write');
        $parcelid = $writeConnection->fetchOne("SELECT sendcloud_parcel_id FROM " . $table . " WHERE entity_id = " . $id);
//        echo date('H:i:s')." - SELECT sendcloud_parcel_id FROM " . $table . " WHERE entity_id = " . $id ."\n          With result:".$parcelid."\n";//' [CHECK IN DB] Result is '.$parcelid ."\n";
        return $parcelid;
    }

    public function update($order, $parcelid) {
        $id = (int)$order->getId();

        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('sales/order');

        // Apply the update directly via query, because saving another time can create nasty problems.
        $writeConnection = $resource->getConnection('core_write');
        $writeConnection->query('UPDATE ' . $table . ' SET sendcloud_parcel_id = '.(int)$parcelid.' WHERE entity_id = ' . $id . ' LIMIT 1');

        $cache = Mage::app()->getCache();
        // 120 seconds should be sufficient for our purposes
        $cache->save((int)$parcelid."", "sendcloud_order_".$id, array("sendcloud"), 120);

//        echo date('H:i:s'). ' - UPDATE ' . $table . ' SET sendcloud_parcel_id = '.(int)$parcelid.' WHERE entity_id = ' . $id . " LIMIT 1\n";//' [SET IN DB] Setting '.$id.' to '.(int)$parcelid . "\n";
        $this->check($order);
    }
}
