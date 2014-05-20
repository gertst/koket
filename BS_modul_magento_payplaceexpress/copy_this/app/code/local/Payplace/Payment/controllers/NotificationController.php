<?php

class Payplace_Payment_NotificationController extends Mage_Core_Controller_Front_Action {


	/**
	 * Instantiate IPN model and pass IPN request to it
	 */
	public function indexAction()
	{
		if (!$this->getRequest()->isPost()) {
			return;
		}

		try {
			$data = $this->getRequest()->getPost();


			$dStatus=Mage::getModel('Payplace_payment/Notification')->processIpnRequest($data);
			echo $dStatus['redirecturl'];
			$this->_response->setBody($dStatus['redirecturl']);
			$this->_response->sendResponse();

		} catch (Exception $e) {
			Mage::logException($e);
		}
	}


}