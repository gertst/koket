<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'sendcloud_service_point', 'text');
$installer->endSetup();
