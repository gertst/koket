<?php

class Payplace_Payment_Model_Base extends Mage_Payment_Model_Method_Abstract {

	const  PREFIX_CC='CC';
	const  PREFIX_PP='PP';
	const  PREFIX_GP='GP';
	const  PREFIX_DD='DD';
	const  PREFIX_BASE='BASE';


	const MANDATEID='mandateid';
	const MANDATEPREFIX='mandateprefix';
	const MANDATENAME='mandatename';
	const SEQUENCETYPE='sequencetype';


	const DEBUG_FILE_PATH='debug_file_path';
	const DEBUG='debug';

	const TITLE='title';
	const PUBLIC_TITLE='public_title';
	const SORT_ORDER='sort_order';
	const STATUS='status';

	const DESCRIPTION='description';
	const ORDER_STATUS_ID='ORDER_STATUS_ID';
	const ZONE='ZONE';

	const SECRET='secret';
	const SSLMERCHANT='sslmerchant';

	const TEST_MODE='test_mode';

	const ACCEPTCOUNTRIES='acceptcountries';
	const REJECTCOUNTRIES='rejectcountries';

	const TRANSACTIONTYPE='transactiontype';
	const NOTIFICATION_FAILED_URL='notificationfailedurl';
	const NOTIFYURL='notifyurl';
	const CSS_URL='cssurl';
	const AUTOCAPTURE='autocapture';
	const COUNTRYREJECTMESSAGE='countryrejectmessage';
	const FORM_MERCHANTNAME='form_merchantname';
	const DELIVERYCOUNTRY_ACTION='deliverycountry_action';
	const FORM_LABEL_SUBMIT='form_label_submit';
	const FORM_LABEL_CANCEL='form_label_cancel';
	const DELIVERYCOUNTRY_REJECT_MESSAGE='deliverycountry_reject_message';
	const FORM_MERCHANTREF='form_merchantref';
	const PAYMENT_GATEWAY_URL='payment_gateway_url';
	const PAYMENTOPTIONS='payment_options';

	const LABEL0='label0';
	const LABEL1='label1';
	const LABEL2='label2';
	const LABEL3='label3';
	const LABEL4='label4';

	const TEXT0='text0';
	const TEXT1='text1';
	const TEXT2='text2';
	const TEXT3='text3';
	const TEXT4='text4';

	/////////////////


	protected $_code = 'Payplace_payment_base';

	protected $_isInitializeNeeded      = true;
	protected $_canUseForMultishipping  = false;
	protected $_canUseInternal          = false;

	protected $_order;





	protected $prefix = self::PREFIX_CC;


	//common transaction fields
	protected $paymentmethod;

	protected $logger;


	/**
	 * constructor
	 */
	public function __construct(){
		$this->logger=new Payplace_Payment_Helper_SimpleLogger($this->getLoggerFileName(),$this->getLoggerLevel());
	}


	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('Payplace_payment/payment/redirect', array('_secure' => true));
	}


	public function getPaymentMethod(){
		return $this->paymentmethod;
	}

	public function isLiveMode(){
		return !$this->getValueforKey(self::TEST_MODE);
	}

	function getSecret(){
		return $this->getValueforCommonKey(self::SECRET);
	}

	function getPrefix(){
		return $this->prefix;
	}

	function getSSLMerchant(){
		return $this->getValueforCommonKey(self::SSLMERCHANT);
	}

	function getTransactiontype(){
		return $this->getValueforKey(self::TRANSACTIONTYPE);
	}

	/**
	 * Instantiate state and set it to state object
	 * @param string $paymentAction
	 * @param Varien_Object
	 */
	public function initialize($paymentAction, $stateObject)
	{
		$state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
		$stateObject->setState($state);
		$stateObject->setStatus('pending_payment');
		$stateObject->setIsNotified(false);
	}




	function init(){}


	function getLoggerLevel(){
		$debug=$this->getValueforCommonKey(self::DEBUG);

		if($debug =='debug')
		{
			return 'DEBUG';
		}

		if($debug =='info')
		{
			return 'info';
		}

		return 'NONE';
	}

	function getLoggerFileName(){
		$debug=$this->getValueforCommonKey(self::DEBUG_FILE_PATH);
		return $debug;
	}

	function getCssurl()
	{
		if(defined('CSS_URL') && !is_null(CSS_URL))
		{
			return CSS_URL;
		}

		return null;
	}



	function getOrderid(){
		return $this->_order->getId();

	}


	function getPaymentGatewayURL() {
		return $this->getValueforKey(self::PAYMENT_GATEWAY_URL);
	}

	function getPayment_options() {
		return $this->getValueforKey(self::PAYMENTOPTIONS);
	}



	/* (non-PHPdoc)
	 * @see Core#setAdditionalParamsforPayPal($params)
	*/
	function setAdditionalParamsforPayPal(array &$params){
		$line_item_no = 0;

		$items = $this->_order->getAllItems();
		$itemcount=count($items);

		$name=array();
		$unitPrice=array();
		$sku=array();
		$ids=array();
		$qty=array();

		foreach ($items as $itemId => $item)
		{
			$sku[]=$item->getSku();
		}

		$params['basket_shipping_costs']=$this->formatAmount($this->_order->getShippingAmount());

		foreach ($items as $itemId => $item) {
			$params['basketitem_amount' . $line_item_no] = $this->formatAmount($item->getPrice());
			$params['basketitem_name' . $line_item_no] =substr($item->getName(),0,32);
			$params['basketitem_desc' . $line_item_no] =substr($product['name'],0,50);
			$params['basketitem_number' . $line_item_no] = urlencode(substr($ids[]=$item->getProductId(),0,32));
			$params['basketitem_qty' . $line_item_no] = $item->getQtyToInvoice();

			$line_item_no++;
		}

		return $params;
	}



	/**
	 * @param unknown_type $order_id
	 */
	function processOnError($order_id,$msg){
		$_order = new Mage_Sales_Model_Order();
		$_order = Mage::getModel('sales/order')	->load($order_id);
		$_order->cancel();
		$_order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $msg);
		$_order->save();
		return Mage::getUrl('checkout/onepage/failure');
	}



	function processOnCancel($order_id){
		$_order = new Mage_Sales_Model_Order();
		$_order = Mage::getModel('sales/order')	->load($order_id);
		$_order->cancel();
		$_order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
		$_order->save();
		return Mage::getUrl('checkout/onepage/failure');

		//return 	Mage::getUrl('Payplace_payment/Notification/cancel');

	}


	function processOnOk($order_id){

		//$this->logDebug("processOnOk()->start()");
		$_order = new Mage_Sales_Model_Order();
		$_order = Mage::getModel('sales/order')->load($order_id);
		$_order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_COMPLETE)->save();
		$_order->sendNewOrderEmail();
		return Mage::getUrl('checkout/onepage/success');
	}




	/* (non-PHPdoc)
	 * @see Core::getAmount()
	*/
	function getAmount(){
		$amount =$this->_order->getGrandTotal();
		return $this->formatAmount($amount);
	}

	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function getForm_label_submit(){
		return $this->getValueforCommonKey(self::FORM_LABEL_SUBMIT);
	}
	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function getForm_label_cancel(){
		return $this->getValueforCommonKey(self::FORM_LABEL_CANCEL);
	}

	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function getDeliverycountryrejectmessage(){
		return $this->getValueforCommonKey(self::DELIVERYCOUNTRY_REJECT_MESSAGE);
	}

	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function getForm_merchantref(){
		return $this->getValueforKey(self::FORM_MERCHANTREF);
	}




	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function  getDeliverycountryaction(){
		return $this->getValueforKey(self::DELIVERYCOUNTRY_ACTION);
	}


	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function getAutocapture(){
		return $this->getValueforKey(self::AUTOCAPTURE);
	}

	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function getCountryrejectmessage(){
		return $this->getValueforCommonKey(self::COUNTRYREJECTMESSAGE);
	}

	/**
	 * Enter description here ...
	 * @return Ambigous <NULL, mixed>
	 */
	function getForm_merchantname(){
		return $this->getValueforCommonKey(self::FORM_MERCHANTNAME);
	}

	/* (non-PHPdoc)
	 * @see Core::getCurrency()
	*/
	function getCurrency(){
		return  Mage::app()->getStore()->getCurrentCurrencyCode();
	}

	/* (non-PHPdoc)
	 * @see Core::getLocale()
	*/
	function getLocale(){
		$locale = Mage::app()->getLocale()->getLocaleCode();
		return $locale;
	}

	/* (non-PHPdoc)
	 * @see Core::getSessionid()
	*/
	function getSessionid(){
		return Mage::getSingleton("core/session")->getEncryptedSessionId();
	}


	/* (non-PHPdoc)
	 * @see Core::getBasketid()
	*/
	function getBasketid(){
		return $this->prefix.$this->_order->getQuoteId();
	}

	/* (non-PHPdoc)
	 * @see Core::getNotifyurl()
	*/
	function getNotifyurl(){
		return Mage::getUrl('Payplace_payment/Notification/');
		return $this->getValueforCommonKey('NOTIFICATION_URL');
	}

	/* (non-PHPdoc)
	 * @see Core::getNotificationfailedurl()
	*/
	function getNotificationfailedurl(){
		//return Mage::getUrl('Payplace_payment/Notification/');
		return $this->getValueforCommonKey(self::NOTIFICATION_FAILED_URL);
	}




	#****************************** helber functions ***************

	/**
	 * Enter description here ...
	 * @param unknown_type $amount
	 * @return string
	 */
	private function formatAmount($amount){
		// set the amount
		$tstr = number_format($amount, 2, ',', '');
		$tstr = substr( $tstr, 0,strpos($tstr,',')+3);
		return $tstr;
	}



	/**
	 * Enter description here ...
	 */
	function getZone(){
		$this->getValueforKey('ZONE');
	}

	/**
	 * Enter description here ...
	 * @param unknown_type $Key
	 * @return mixed|NULL
	 */
	function getValueforKey($Key){
		$configKey='payment/'.$this->getCode().'/'.$Key;
		$value=$this->getConfigData($Key);
		//$this->logDebug("value for ".$configKey." is:".$value);
		return $value;
	}

	/**
	 * Enter description here ...
	 * @param unknown_type $Key
	 * @return mixed|NULL
	 */
	function getValueforCommonKey($Key){
		$configKey='payment/Payplace_payment/'.$Key;
		$value=mage::getStoreConfig($configKey);
		//$this->logDebug("value for ".$configKey." is:".$value);
		return $value;
	}


	function getTransactionRedirect($order){
		$this->_order=$order;
		$helper=new Payplace_Payment_Helper_PayplaceCore();
		return $helper->getTransactionRedirect($this);
	}

	public function processIpnRequest(array $data){
		$helper=new Payplace_Payment_Helper_PayplaceCore();
		return $helper-> processPaymentGatewayNotification($data, $this);
	}

	/**
	 * @param unknown $param
	 */
	function logDebug($param) {
		$this->logger->debug($param);
	}

	/**
	 * @param unknown $param
	 */
	function logTransaction($param) {
		$this->logger->info($param);
	}

	/**
	 * @param unknown $param
	 */
	function logError($param) {
		$this->logger->error($param);
	}

}
?>