<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    Mage_ManualCardPayment
 * @copyright  Copyright (c) 2008 Andrej Sinicyn, Mik3e
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_ManualCardPayment_Model_ManualCardPayment extends Mage_Payment_Model_Method_Abstract
{

    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'manualcardpayment';

    protected $_formBlockType = 'manualCardPayment/form';
    protected $_infoBlockType = 'manualCardPayment/info';
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = false;

    public function getCODTitle()
    {
        return $this->getConfigData('title');
    }

    public function getInlandCosts()
    {
        return floatval($this->getConfigData('inlandcosts'));
    }

    public function getForeignCountryCosts()
    {
        return floatval($this->getConfigData('foreigncountrycosts'));
    }

    public function getCustomText()
    {
        return $this->getConfigData('customtext');
    }

}
