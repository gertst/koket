<?php
class Payplace_Payment_Model_Cc extends Payplace_Payment_Model_Base {
	protected $_code = 'Payplace_payment_cc';

	protected $paymentmethod='creditcard';
	protected $prefix = self::PREFIX_CC;

	function getAcceptcountries(){
		return $this->getValueforKey(self::ACCEPTCOUNTRIES);
	}

	function getRejectcountries(){
		return $this->getValueforKey(PayplacePaymentIF::REJECTCOUNTRIES);
	}

	function getCustomer_addr_city(){
		return $this->_order->getShippingaddress()->getCity();
	}

	function getCustomer_addr_street(){
		return $this->_order->getShippingaddress()->getData('street');
	}

	function getCustomer_addr_zip(){
		return $this->_order->getShippingaddress()->getZip();
	}

	function getCustomer_addr_number(){
		global $order;
		return '';
	}

	function getDeliverycountry(){
		return $this->_order->getShippingaddress()->getCountryCode();
	}
}
?>