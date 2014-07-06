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

class Mage_ManualCardPayment_Model_Quote extends Mage_Sales_Model_Quote
{

    public function getTotals()
    {

        /*
         * Get array from parent
         */
        $res = parent::getTotals();

        /*
         * Set COD name
         */
        if (isset($res['shipping']) && is_object($res['shipping'])) {
            if (!is_null($this->_payments) && $this->getPayment()->hasMethodInstance() && $this->getPayment()->getMethodInstance()->getCode() == 'manualcardpayment') {
                $cod = Mage::getModel('manualCardPayment/manualCardPayment');
                $res['shipping']->setData('title',$res['shipping']->getData('title') . ' + ' . $cod->getCODTitle());
            }
        }

        return $res;
    }

    public function collectTotals()
    {

        $cod = Mage::getModel('manualCardPayment/manualCardPayment');
        $res = parent::collectTotals();
        $codamount = 0;

        /*
         * Check if COD is selected
         */

        if (!is_null($this->_payments) && $this->getPayment()->hasMethodInstance() && $this->getPayment()->getMethodInstance()->getCode() == 'manualcardpayment') {

            /*
             * Calculate cost
             */
            foreach ($res->getAllShippingAddresses() as $address) {

                /*
                 * Save old shipping taxes
                 */
                $oldTax     = $address->getShippingTaxAmount();
                $oldBaseTax = $address->getBaseShippingTaxAmount();

                /*
                 * Add COD cost
                 */
                if ($this->getShippingAddress()->getCountry() == Mage::getStoreConfig('shipping/origin/country_id')) {
                    $codamount = $cod->getInlandCosts();
                } else {
                    $codamount = $cod->getForeignCountryCosts();
                }
                $address->setShippingAmount($address->getShippingAmount() + $address->getShippingTaxAmount() + $codamount);
                $address->setBaseShippingAmount($address->getBaseShippingAmount() + $address->getBaseShippingTaxAmount() + $codamount);

                /*
                 * Recalculate tax for shipping including COD
                 */
                $store = $address->getQuote()->getStore();
                $shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);
                if ($shippingTaxClass) {
                    $custTaxClassId = $address->getQuote()->getCustomerTaxClassId();
                    $taxCalculationModel = Mage::getSingleton('tax/calculation');
                    $request = $taxCalculationModel->getRateRequest($address, $address->getQuote()->getBillingAddress(), $custTaxClassId, $store);

                    if ($rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass))) {
                        if (!Mage::helper('tax')->shippingPriceIncludesTax()) {
                            $shippingTax    = $address->getShippingAmount() * $rate/100;
                            $shippingBaseTax= $address->getBaseShippingAmount() * $rate/100;

                            $address->setShippingTaxAmount($shippingTax);
                            $address->setBaseShippingTaxAmount($shippingBaseTax);
                        } else {
                            $shippingTax    = $address->getShippingTaxAmount();;
                            $shippingBaseTax= $address->getBaseShippingTaxAmount();
                        }

                        $shippingTax    = $store->roundPrice($shippingTax);
                        $shippingBaseTax= $store->roundPrice($shippingBaseTax);

                        $address->setTaxAmount($address->getTaxAmount() - $oldTax + $shippingTax);
                        $address->setBaseTaxAmount($address->getBaseTaxAmount() - $oldBaseTax + $shippingBaseTax);

                        $this->_saveAppliedTaxes(
                            $address,
                            $taxCalculationModel->getAppliedRates($request),
                            $shippingTax - $oldTax,
                            $shippingBaseTax - $oldBaseTax,
                            $rate
                        );
                    }
                }

                $address->setBaseGrandTotal($address->getBaseGrandTotal() + $codamount);
                $address->setGrandTotal($address->getGrandTotal() + $codamount);

            }
        }

        return $res;

    }

    protected function _saveAppliedTaxes(Mage_Sales_Model_Quote_Address $address, $applied, $amount, $baseAmount, $rate)
    {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }


            $row['percent'] = $row['percent'] ? $row['percent'] : 1;
            $rate = $rate ? $rate : 1;

            $appliedAmount = $amount/$rate*$row['percent'];
            $baseAppliedAmount = $baseAmount/$rate*$row['percent'];

            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }

}