<?php
class Ampersand_BillingAgreementFix_Rewrite_Mage_Sales_Model_Billing_Agreement
    extends Mage_Sales_Model_Billing_Agreement
{
    /**
     * Rewritten to check for a specific payment method.
     *
     * @param int $customerId
     * @return bool
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function needToCreateForCustomer($customerId)
    {
        // changing the accepted params would cause PHP Strict error due to parent declaration
        $methodCode = func_get_arg(1);
        
        if (!$customerId || !$methodCode) {
            return false;
        }
        
        $collection = $this->getAvailableCustomerBillingAgreements($customerId)
            ->addFieldToFilter('method_code', $methodCode);
        
        return $collection->count() == 0;
    }
}