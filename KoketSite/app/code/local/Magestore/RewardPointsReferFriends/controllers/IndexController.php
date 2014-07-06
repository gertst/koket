<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsReferFriends Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * index action
     */
    public function indexAction() {
        if(!Mage::helper('rewardpointsreferfriends')->isEnable(Mage::app()->getStore()->getId()))
            return $this->_redirect("");
         if(!Mage::helper('customer')->isLoggedIn()) return $this->_redirect ('customer/account/');
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Retrieve gmail contact
     * @return type
     */
    public function gmailAction() {
        if(!Mage::helper('rewardpointsreferfriends')->isEnable(Mage::app()->getStore()->getId()))
            return $this->_redirect("");
        if (!Mage::Helper('customer')->isLoggedIn()) {
            $url = Mage::getUrl('customer/account/');
            echo "<html><head></head><body><script type='text/javascript'>
				try{
					window.opener.location.href = '$url';
				}catch(e){}
		    	window.close();
	    	</script></body></html>";
            exit();
        }
        $gmail = Mage::getSingleton('rewardpointsreferfriends/refer_gmail');
        if (!$gmail->isAuth())
            return $this->_redirectUrl($gmail->getAuthUrl());

        $this->loadLayout();
        $this->renderLayout();
    }
    /**
     * Retrieve yahoo contact
     * @return type
     */
    public function yahooAction() {
        if(!Mage::helper('rewardpointsreferfriends')->isEnable(Mage::app()->getStore()->getId()))
            return $this->_redirect("");
        if (!Mage::Helper('customer')->isLoggedIn()) {
            $url = Mage::getUrl('customer/account/');
            echo "<html><head></head><body><script type='text/javascript'>
				try{
					window.opener.location.href = '$url';
				}catch(e){}
		    	window.close();
	    	</script></body></html>";
            exit();
        }
        $yahoo = Mage::getSingleton('rewardpointsreferfriends/refer_yahoo');
        if (!$yahoo->hasSession() || !$this->getRequest()->getParam('oauth_token') || !$this->getRequest()->getParam('oauth_verifier'))
            return $this->_redirectUrl($yahoo->getAuthUrl());

        $this->loadLayout();
        $this->renderLayout();
    }
    /**
     * Share facebook
     */
    public function facebookAction() {
        if(!Mage::helper('rewardpointsreferfriends')->isEnable(Mage::app()->getStore()->getId()))
            return $this->_redirect("");
        if (!Mage::Helper('customer')->isLoggedIn()) {
            $url = Mage::getUrl('customer/account/');
            echo "<html><head></head><body><script type='text/javascript'>
				try{
					window.opener.location.href = '$url';
				}catch(e){}
		    	window.close();
	    	</script></body></html>";
            exit();
        }
        try {
            $isAuth = $this->getRequest()->getParam('auth');
            if (!class_exists('Facebook'))
                require_once(Mage::getBaseDir('lib') . DS . 'Facebookv3' . DS . 'facebook.php');
            $facebook = new Facebook(array(
                'appId' => Mage::helper('rewardpointsreferfriends')->getReferConfig('fbapp_id'),
                'secret' => Mage::helper('rewardpointsreferfriends')->getReferConfig('fbapp_secret'),
                'cookie' => true
            ));
            $userId = $facebook->getUser();
            if ($isAuth || !$userId) {
                $loginUrl = $facebook->getLoginUrl(array(
                    'display' => 'popup',
                    'redirect_uri' => Mage::getUrl('*/*/facebook'),
                    'scope' => 'publish_stream,email',
                ));
                unset($_SESSION['fb_' . $this->_getHelper()->getReferConfig('fbapp_id') . '_code']);
                unset($_SESSION['fb_' . $this->_getHelper()->getReferConfig('fbapp_id') . '_access_token']);
                unset($_SESSION['fb_' . $this->_getHelper()->getReferConfig('fbapp_id') . '_user_id']);
                die("<script type='text/javascript'>top.location.href = '$loginUrl';</script>");
            }
            $params = $this->getRequest()->getParams();
            if (!isset($params['message'])) {
                echo "<html><head></head><body><script type='text/javascript'>
				var newUrl = window.location.href;
				var message = '';
				try{
					message = window.opener.document.getElementById('referfriends-facebook-content').value;
					message = encodeURIComponent(message);
				}catch(e){}
				var fragment = '';
				if (newUrl.indexOf('#')){
					fragment = '#' + newUrl.split('#')[1];
					newUrl = newUrl.split('#')[0];
				}
				if (newUrl.indexOf('?') != -1) newUrl += '&message=' + message;
				else newUrl += '?message=' + message;
				newUrl += fragment;
				top.location.href = newUrl;
				</script></body></html>";
                exit();
            }
            $message = $params['message'];
            if (!$message)
                $message = Mage::getBlockSingleton('rewardpointsreferfriends/rewardpointsreferfriends')->getFacebookContent();

            $facebook->api("/$userId/feed", 'POST', array('message' => $message));

            echo "<script type='text/javascript'>window.opener.document.getElementById('referfriends-facebook-msg').show();
                window.close();</script>";
            exit();
        } catch (Exception $e) {
            echo "<script type='text/javascript'>window.opener.document.getElementById('referfriends-facebook-msg-fail').show();
                window.close();</script>";
        }
        echo "<script type='text/javascript'>window.opener.document.getElementById('referfriends-facebook-msg-fail').show();
            window.close();</script>";
        exit();
    }
    /**
     * Get Helper
     * @return type
     */
    function _getHelper() {
        return Mage::helper('rewardpointsreferfriends');
    }

}
