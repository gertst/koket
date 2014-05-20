<?php

interface PayplacePaymentIF{


	const  PREFIX_CC='CC';
	const  PREFIX_PP='PP';
	const  PREFIX_GP='GP';
	const  PREFIX_DD='DD';
	const  PREFIX_SE='SE';
	const  PREFIX_BASE='BASE';




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

	const MANDATEID='mandateid';
	const MANDATEPREFIX='mandateprefix';
	const MANDATENAME='mandatename';
	const SEQUENCETYPE='sequencetype';


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




	function getLoggerFileName();

	function getSSLMerchant();

	function getTransactiontype();

	function getCssurl();

	function isLiveMode();

	function getSecret();

	function getPrefix();

	function getLoggerLevel();

	function getPaymentGatewayURL();
	/**
	 * Enter description here ...
	*/
	function getOrderid();

	function getPaymentMethod();

	/**
	 * Enter description here ...
	*/
	function getLocale();

	/**
	 * Enter description here ...
	*/
	function getBasketid();

	/**
	 * Enter description here ...
	*/
	function getAmount();
	/**
	 * Enter description here ...
	*/
	function getCurrency();
	/**
	 * Enter description here ...
	*/
	function getSessionid();

	/**
	 * Enter description here ...
	*/
	function getNotifyurl();
	/**
	 * Enter description here ...
	*/
	function getNotificationfailedurl();

	function processOnError($order_id,$msg);

	function processOnCancel($order_id);

	function processOnOk($order_id);

	/**
	 * @param unknown $param
	*/
	function logDebug($param);

	/**
	 * @param unknown $param
	*/
	function logTransaction($param);

	/**
	 * @param unknown $param
	*/
	function logError($param);


}

?>