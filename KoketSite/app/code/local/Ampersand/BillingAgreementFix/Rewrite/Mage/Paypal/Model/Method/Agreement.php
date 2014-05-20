<?php
class Ampersand_BillingAgreementFix_Rewrite_Mage_Paypal_Model_Method_Agreement
    extends Mage_Paypal_Model_Method_Agreement
{
    /**
     * Rewritten to include the method code in the call to getAvailableCustomerBillingAgreements().
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function isAvailable($quote = null)
    {
        // we need to call the parent first otherwise it will override our checks below
        $this->_isAvailable = parent::isAvailable($quote);
        
        if (is_object($quote) && $quote->getCustomer()) {
            $availableBA = Mage::getModel('sales/billing_agreement')
                ->getAvailableCustomerBillingAgreements($quote->getCustomer()->getId())
                ->addFieldToFilter('method_code', $this->_code);
            $isAvailableBA = count($availableBA) > 0;
            $this->_canUseCheckout = $this->_canUseInternal = $isAvailableBA;
        }
        
        return $this->_isAvailable;
    }
}