<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('sales/shipment'), 'sendcloud_parcel_id', 'int');
$installer->getConnection()->addColumn($installer->getTable('sales/shipment'), 'sendcloud_parcel_status_id', 'int');
$installer->endSetup();
