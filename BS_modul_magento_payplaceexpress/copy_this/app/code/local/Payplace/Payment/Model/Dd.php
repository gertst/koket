<?php
class Payplace_Payment_Model_Dd extends Payplace_Payment_Model_Base {
	protected $_code = 'Payplace_payment_dd';
	protected $prefix = self::PREFIX_DD;

	protected $paymentmethod='directdebit';
	function getAcceptcountries(){
		return 'DE';
	}

	function getMandateid() {
		return $this->getValueforKey(self::MANDATEPREFIX).'-'.$this->getOrderid();
	}
	function getMandatename() {
		return $this->getValueforKey(self::MANDATENAME);
	}

	function getMandatesigned() {
		return date('Ymd');
	}

	function getSequencetype() {
		return $this->getValueforKey(self::SEQUENCETYPE);
	}
}
?>