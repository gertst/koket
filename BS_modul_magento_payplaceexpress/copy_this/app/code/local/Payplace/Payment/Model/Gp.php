<?php
class Payplace_Payment_Model_Gp extends Payplace_Payment_Model_Base {
	protected $_code = 'Payplace_payment_gp';
	protected $paymentmethod='banktransfer';
	protected $prefix = self::PREFIX_GP;

	function getLabel0($param='') {
		return $this->getValueforKey(PayplacePaymentIF::LABEL0);
	}
	function getLabel1($param='') {
		return $this->getValueforKey(PayplacePaymentIF::LABEL1);
	}
	function getLabel2($param='') {
		return $this->getValueforKey(PayplacePaymentIF::LABEL2);
	}
	function getLabel3($param='') {
		return $this->getValueforKey(PayplacePaymentIF::LABEL3);
	}
	function getLabel4($param='') {
		return $this->getValueforKey(PayplacePaymentIF::LABEL4);
	}

	function getText0($param='') {
		return $this->getValueforKey(PayplacePaymentIF::TEXT0);
	}
	function gettext1($param='') {
		return $this->getValueforKey(PayplacePaymentIF::TEXT1);
	}
	function getText2($param='') {
		return $this->getValueforKey(PayplacePaymentIF::TEXT2);
	}
	function getText3($param='') {
		return $this->getValueforKey(PayplacePaymentIF::TEXT3);
	}
	function getText4($param='') {
		return $this->getValueforKey(PayplacePaymentIF::TEXT4);
	}

	function getAccountNumber(){
		return '';
	}

	function getBankCode(){
		if(!$this->isLiveMode())
		return '12345679';
		else
			return '';
	}

	function getPayment_options(){
		$paymentoptions='';
		if($this->getValueforKey('ageverification'))
			$paymentoptions= 'avsopen';

		return $paymentoptions ;
	}

	function getBic(){
		if($this->isLiveMode())
		return '';
		else
		return 'TESTDETT421';
	}

	function getIban(){
		return '';
	}

}
?>