<?php
class Ampersand_BillingAgreementFix_Rewrite_Mage_Paypal_Helper_Data extends Mage_Paypal_Helper_Data
{
    /**
     * Rewritten to check for a specific payment method.
     *
     * @param Mage_Paypal_Model_Config $config
     * @param int $customerId
     * @return bool
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function shouldAskToCreateBillingAgreement(Mage_Paypal_Model_Config $config, $customerId)
    {
        if (null === self::$_shouldAskToCreateBillingAgreement) {
            self::$_shouldAskToCreateBillingAgreement = false;
            if ($customerId && $config->shouldAskToCreateBillingAgreement()) {
                $methodCode = Mage_Paypal_Model_Config::METHOD_BILLING_AGREEMENT;
                $needToCreateForCustomer = Mage::getModel('sales/billing_agreement')
                    ->needToCreateForCustomer($customerId, $methodCode);
                
                if ($needToCreateForCustomer) {
                    self::$_shouldAskToCreateBillingAgreement = true;
                }
            }
        }
        return self::$_shouldAskToCreateBillingAgreement;
    }
}