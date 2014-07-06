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
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Earning Catalog Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Earning_Catalog extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_earning_catalog';
        $this->_blockGroup = 'rewardpointsrule';
        $this->_headerText = Mage::helper('rewardpointsrule')->__('Catalog Earning Rule Manager');
        //$this->_applyRuleLabel = Mage::helper('rewardpointsrule')->__('Apply Rules');
        
        $this->_addButtonLabel = Mage::helper('rewardpointsrule')->__('Add Rule');
        
        $this->_addButton('applyRuleLabel', array(
            'label'     => Mage::helper('rewardpointsrule')->__('Apply Rules'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/applyRules') .'\')',
            'class'     => '',
        ));
        
        parent::__construct();
    }
}
