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
 * @package     Magestore_RewardPointsBehavior
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsbehavior Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Block_Showrewardpoint extends Mage_Core_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_RewardPointsBehavior_Block_Rewardpointsbehavior
     */
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardNewsletterInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getSignConfig('newsletter'));
        return $this->_helper()->__('Earn %s for subscribing to newsletter. ', $point);
    }

    /**
     * get message earn points for signing up account
     * @return string
     */
    public function getSignupInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getSignConfig('signing_up'));
        return $this->_helper()->__('Earn %s for registering an account. a new account', $point);
    }

    public function getSignupInfoNew() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getSignConfig('signing_up'));
        return $this->_helper()->__('Earn %s for registering an account.', $point);
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardPollInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getPollConfig('poll'));
        return $this->_helper()->__('Earn %s for taking poll. ', $point);
    }

    /**
     * get product
     * @return current product
     */
    public function getProduct() {
        return Mage::registry('current_product');
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardTagProductInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getTagConfig('tag'));
        return $this->_helper()->__('You will earn %s for writing a tag for this product.', $point);
    }

    /**
     * check rate, review is enable
     * @return int
     */
    public function isRateReview() {
        $count = 0;
        if ($this->_helper()->getRateConfig('rate') && $this->_helper()->getRateConfig('show_rate_product'))
            $count++;
        if ($this->_helper()->getReviewConfig('review') && $this->_helper()->getReviewConfig('show_reviewing'))
            $count++;
        return $count;
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardViewProductInFo() {
        $pointrate = Mage::helper('rewardpoints/point')->format($this->_helper()->getRateConfig('rate'));
        $pointreview = Mage::helper('rewardpoints/point')->format($this->_helper()->getReviewConfig('review'));
        $message = '';
        if ($this->isRateReview() == 2) {
            $message = $this->_helper()->__('You will earn %s for writing a review and %s for rating this product.', $pointreview, $pointrate);
        } else if ($this->isRateReview() == 1) {
            if ($this->_helper()->getRateConfig('show_rate_product') && $this->_helper()->getRateConfig('rate')) {
                $message = $this->_helper()->__('You will earn %s for rating this product.', $pointrate);
            } else {
                $message = $this->_helper()->__('You will earn %s for writing a review this product.', $pointreview);
            }
        }
        return $message;
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getBirthdayInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getBirthdayConfig('customer_birthday'));
        return $this->_helper()->__('You will earn %s on your birthday.', $point);
    }

    /**
     * check enable plugin
     * @return boolean
     */
    public function isEnabled() {
        if (!$this->_helper()->isEnable($this->_getStore()))
            return false;
        //if (!Mage::getSingleton('customer/session')->isLoggedIn())
        //   return false;
        return true;
    }

    public function _helper() {
        return Mage::helper('rewardpointsbehavior');
    }

    public function getSocialInfo() {
        $face = $this->showLikeFacebook();
        $facepoint = Mage::helper('rewardpoints/point')->format($this->_helper()->getFacebookConfig('fb_like_earn'));
        $facesend = $this->showSendFacebook();
        $facesendpoint = Mage::helper('rewardpoints/point')->format($this->_helper()->getFacebookConfig('fb_share_earn'));
        $twit = $this->showTwitter();
        $twitpoint = Mage::helper('rewardpoints/point')->format($this->_helper()->getTwitterConfig('tw_earn'));
        $google = $this->showGoogle();
        $googlepoint = Mage::helper('rewardpoints/point')->format($this->_helper()->getGoogleConfig('gg_earn'));
        if ($face && !$twit && !$google) {
            if ($facesend)
                return $this->_helper()->__('You will earn %s for a Facebook like and %s for sending links to friend via facebook', $facepoint, $facesendpoint);
            return $this->_helper()->__('You will earn %s for a Facebook like.', $facepoint);
        }
        if (!$face && $twit && !$google)
            return $this->_helper()->__('You will earn %s for a Twitter tweet.', $twitpoint);
        if (!$face && !$twit && $google)
            return $this->_helper()->__('You will earn %s for a Google plus +1.', $googlepoint);
        return $this->_helper()->__('Like or share to receive points.');
    }

    public function showLikeFacebook() {
        return $this->_helper()->getFacebookConfig('show_fb_like');
    }

    public function showCountLikeFacebook() {
        return $this->_helper()->getFacebookConfig('show_fb_count');
        return true;
    }

    public function showSendFacebook() {
        return false;
        return $this->_helper()->getFacebookConfig('show_fb_share');
    }

    public function showTwitter() {
        return $this->_helper()->getTwitterConfig('show_tw_tweet');
    }

    public function showCountTwitter() {
        return $this->_helper()->getTwitterConfig('show_tw_count');
    }

    public function showGoogle() {
        return $this->_helper()->getGoogleConfig('show_gg_button');
    }

    public function showCountGoogle() {
        return $this->_helper()->getGoogleConfig('show_gg_count');
    }

    //show key
    public function showKey() {
        if (Mage::getConfig()->getModuleConfig('Magestore_RewardPointsReferFriends')->is('active', 'true')
                && Mage::helper('rewardpointsreferfriends')->isEnable()) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->getCollection()
                        ->addFieldToFilter('customer_id', $customerId);
                $product = $model->getFirstItem();
                $key = $product->getData('key');
                return '/?k='.$key;
            }
        } else {
            $key = "";
            return $key;
        }
    }

    public function getCurrentUrl() {
        return Mage::helper('core/url')->getCurrentUrl();
//        return Mage::getUrl('', array(
//                    '_current' => true,
//                    '_use_rewrite' => true,
//                    '_secure' => true,
//        ));
    }

    public function _getStore() {
        return Mage::app()->getStore()->getId();
    }

    public function enableSocial() {
        return Mage::getStoreConfig('rewardpoints/group_social_setting/sc_display', $this->_getStore());
    }

}
