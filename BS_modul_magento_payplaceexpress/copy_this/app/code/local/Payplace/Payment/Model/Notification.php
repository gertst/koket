<?php
class Payplace_Payment_Model_Notification {

	public function processIpnRequest(array $data){
		return $helber= Mage::getModel('Payplace_payment/base')->processIpnRequest($data);
	}
}
?>