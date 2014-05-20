<?php
class Ampersand_BillingAgreementFix_Rewrite_Mage_Sales_Block_Payment_Form_Billing_Agreement
    extends Mage_Sales_Block_Payment_Form_Billing_Agreement
{
    /**
     * Rewritten to restrict billing agreements to the appropriate payment method.
     * 
     * @todo Add versioning to this method once Magento sort the problem
     * @return array
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getBillingAgreements()
    {
        $data = array();
        
        $quote = $this->getParentBlock()->getQuote();
        if (!$quote || !$quote->getCustomer()) {
            return $data;
        }
        
        $collection = Mage::getModel('sales/billing_agreement')
            ->getAvailableCustomerBillingAgreements($quote->getCustomer()->getId())
            ->addFieldToFilter('method_code', $this->getMethod()->getCode());

        foreach ($collection as $item) {
            $data[$item->getId()] = $item->getReferenceId();
        }
        
        return $data;
    }
}