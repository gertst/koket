<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Spending Catalog Adminhtml Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('catalogSpendingRuleGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     * 
     * @return Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('rewardpointsrule/spending_catalog_collection');
        $this->setCollection($collection);
        parent::_prepareCollection();
        foreach ($collection as $offer) {
            $offer->setData('website_ids', explode(',', $offer->getData('website_ids')));
            $offer->setData('customer_group_ids', explode(',', $offer->getData('customer_group_ids')));
        }
        return $this;
    }

    /**
     * prepare columns for this grid
     * 
     * @return type
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header' => Mage::helper('rewardpointsrule')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rule_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('rewardpointsrule')->__('Rule Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header' => Mage::helper('rewardpointsrule')->__('Website'),
                'align' => 'left',
                'width' => '200px',
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'index' => 'website_ids',
                'filter_condition_callback' => array($this, 'filterCallback'),
                'sortable' => false,
            ));
        }

        $this->addColumn('customer_group_ids', array(
            'header' => Mage::helper('rewardpointsrule')->__('Customer Groups'),
            'align' => 'left',
            'index' => 'customer_group_ids',
            'type' => 'options',
            'width' => '200px',
            'sortable' => false,
            'options' => Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash(),
            'filter_condition_callback' => array($this, 'filterCallback'),
        ));

        $this->addColumn('from_date', array(
            'header' => Mage::helper('rewardpointsrule')->__('Created on'),
            'align' => 'left',
            'index' => 'from_date',
            'format' => 'dd/MM/yyyy',
            'type' => 'date',
        ));

        $this->addColumn('to_date', array(
            'header' => Mage::helper('rewardpointsrule')->__('Expired on'),
            'align' => 'left',
            'index' => 'to_date',
            'format' => 'dd/MM/yyyy',
            'type' => 'date',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('rewardpointsrule')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('rewardpointsrule')->__('Active'),
                '0' => Mage::helper('rewardpointsrule')->__('Inactive'),
            ),
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('rewardpointsrule')->__('Priority'),
            'align' => 'left',
            'width' => '60px',
            'index' => 'sort_order',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('rewardpointsrule')->__('Action'),
            'width' => '70px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('rewardpointsrule')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     * 
     * @param type $row
     * @return type
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Callback filter for Website/ Customer group
     * 
     * @param type $collection
     * @param type $column
     * @return type
     */
    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (is_null(@$value))
            return;
        else
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
    }
}
