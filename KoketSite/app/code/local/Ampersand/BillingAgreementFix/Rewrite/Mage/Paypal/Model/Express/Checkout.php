<?php
class Ampersand_BillingAgreementFix_Rewrite_Mage_Paypal_Model_Express_Checkout
    extends Mage_Paypal_Model_Express_Checkout
{
    /**
     * Rewritten to check for a specific payment method.
     *
     * @return Mage_Paypal_Model_Express_Checkout
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _setBillingAgreementRequest()
    {
        if (!$this->_customerId || $this->_quote->hasNominalItems()) {
            return $this;
        }

        $isRequested = $this->_isBARequested || $this->_quote->getPayment()
            ->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);

        if (!($this->_config->allow_ba_signup == Mage_Paypal_Model_Config::EC_BA_SIGNUP_AUTO
            || $isRequested && $this->_config->shouldAskToCreateBillingAgreement())) {
            return $this;
        }
        
        $methodCode = Mage_Paypal_Model_Config::METHOD_BILLING_AGREEMENT;
        $needToCreateForCustomer = Mage::getModel('sales/billing_agreement')
            ->needToCreateForCustomer($this->_customerId, $methodCode);
        
        if (!$needToCreateForCustomer) {
            return $this;
        }
        
        $this->_api->setBillingType($this->_api->getBillingAgreementType());
        
        return $this;
    }
}