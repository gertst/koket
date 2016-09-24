<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'sendcloud_parcel_id', 'int');
$installer->endSetup();