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
 * RewardPointsBehavior Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * index action
     */
    public function indexAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * tweeting on twitter success
     */
    public function tweetAction() {
        //id cua customer vua moi tweet
        //transaction point thanh cong thi: echo 'success';
        $customer_id = $this->getRequest()->getParam('customerid');
        if (!$customer_id)
            return;
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $store_id = $this->getRequest()->getParam('store');
        $total = Mage::helper('rewardpointsbehavior')->getAmountofDay('tweeting', $customer_id);
        $created = Mage::helper('rewardpointsbehavior')->getSocialCreated('tweeting', $customer_id);
        $max_points = Mage::helper('rewardpointsbehavior')->getTwitterConfig('tw_earn_limit', $store_id);
        $tw_earn = Mage::helper('rewardpointsbehavior')->getTwitterConfig('tw_earn', $store_id);
        if ($max_points > 0&&$max_points < ($total + $tw_earn)) {
            $tw_earn = $max_points - $total;
        }
        if ($tw_earn <= 0) {
            echo Mage::helper('rewardpointsbehavior')->__('You can not earn more points today.');
            return;
        }
        $created_time = strtotime($created->getCreatedTime());
        $minium_time = Mage::helper('rewardpointsbehavior')->getTwitterConfig('minSecondsBetweenTweets', $store_id);
        if (($created_time + $minium_time) > strtotime(now())) {
            echo Mage::helper('rewardpointsbehavior')->__('You have to wait at least %s seconds for the next tweet!',$minium_time);
            return;
        }
        $tw_point = array(
            'store_id' => $store_id,
            'tw_earn' => $tw_earn
        );
        try {
            Mage::helper('rewardpoints/action')->addTransaction(
                    'tweeting', $customer, $tw_point
            );
        } catch (Exception $exc) {
            echo $exc->getMessage();
            return;
        }

        echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for tweeting this via Twitter.',Mage::helper('rewardpoints/point')->format($tw_earn));
    }

    /**
     * action earn point for facebook
     * @return type
     */
    public function facebookAction() {
        $action = $this->getRequest()->getParam('action'); //like, send
        $customer_id = $this->getRequest()->getParam('customerid');
        if (!$customer_id)
            return;
        $store_id = $this->getRequest()->getParam('store');
        $link = $this->getRequest()->getParam('link');

        if ($action == 'like') {
            if (Mage::helper('rewardpointsbehavior')->getSocialEarned('fblike', $customer_id, $link)->getFirstItem()->getId())
                return;
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $total = Mage::helper('rewardpointsbehavior')->getAmountofDay('fblike', $customer_id);
            $created = Mage::helper('rewardpointsbehavior')->getSocialCreated('fblike', $customer_id);
            $max_points = Mage::helper('rewardpointsbehavior')->getFacebookConfig('fb_like_earn_limit', $store_id);
            $fb_earn = Mage::helper('rewardpointsbehavior')->getFacebookConfig('fb_like_earn', $store_id);
            if ($max_points > 0 && $max_points < ($total + $fb_earn)) {
                $fb_earn = $max_points - $total;
            }
            if ($fb_earn <= 0) {
                echo Mage::helper('rewardpointsbehavior')->__('You can not earn more points today.');
                return;
            }
            $created_time = strtotime($created->getCreatedTime());
            $minium_time = Mage::helper('rewardpointsbehavior')->getFacebookConfig('minSecondsBetweenLikes', $store_id);
            if (($created_time + $minium_time) > strtotime(now())) {
                echo Mage::helper('rewardpointsbehavior')->__('You have to wait at least %s seconds for the next like!',$minium_time);
                return;
            }
            $fb_point = array(
                'store_id' => $store_id,
                'fb_earn' => $fb_earn,
            );
            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                        'fblike', $customer, $fb_point, $link
                );
            } catch (Exception $exc) {
                echo $exc->getMessage();
                return;
            }

            echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for liking this via Facebook.',Mage::helper('rewardpoints/point')->format($fb_earn));
        }
        if ($action == 'unlike') {
            $earnedlike = Mage::helper('rewardpointsbehavior')->getSocialEarned('fblike', $customer_id, $link);
            $earnedRefund = $earnedlike->getFieldTotal();
            if (!$earnedRefund)
                return;
            $earned = $earnedlike->getFirstItem();
            if ($earned->getStatus() == Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED)
                return;
            $earnedlike->getFirstItem()->cancelTransaction();
            echo '';
            return;
        }
        if ($action == 'send') {
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $total = Mage::helper('rewardpointsbehavior')->getAmountofDay('fbshare', $customer_id);
            $created = Mage::helper('rewardpointsbehavior')->getSocialCreated('fbshare', $customer_id);
            $max_points = Mage::helper('rewardpointsbehavior')->getFacebookConfig('fb_share_earn_limit', $store_id);
            $fb_earn = Mage::helper('rewardpointsbehavior')->getFacebookConfig('fb_share_earn', $store_id);
            if ($max_points > 0&&$max_points < ($total + $fb_earn)) {
                $fb_earn = $max_points - $total;
            }
            if ($fb_earn <= 0) {
                echo Mage::helper('rewardpointsbehavior')->__('You can not earn more points today.');
                return;
            }
            $created_time = strtotime($created->getCreatedTime());
            $minium_time = Mage::helper('rewardpointsbehavior')->getFacebookConfig('minSecondsBetweenLikes', $store_id);
            if (($created_time + $minium_time) > strtotime(now())) {
                echo Mage::helper('rewardpointsbehavior')->__('You have to wait at least %s seconds for the next time of sending link!',$minium_time);
                return;
            }
            $fb_point = array(
                'store_id' => $store_id,
                'fb_earn' => $fb_earn,
            );
            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                        'fbshare', $customer, $fb_point, $link
                );
            } catch (Exception $exc) {
                echo $exc->getMessage();
                return;
            }

            echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for sending link via Facebook.',Mage::helper('rewardpoints/point')->format($fb_earn));
        }
    }

    /**
     * action earn point for google+
     * @return type
     */
    public function googleplusAction() {
        $customer_id = $this->getRequest()->getParam('customerid');
        $store_id = $this->getRequest()->getParam('store');
        $link = $this->getRequest()->getParam('link');
        $gg_plus = $this->getRequest()->getParam('remove'); //on, off
        if (!$customer_id)
            return;
        if ($gg_plus == 'on') {
            if (Mage::helper('rewardpointsbehavior')->getSocialEarned('ggplus', $customer_id, $link)->getFirstItem()->getId())
                return;
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $total = Mage::helper('rewardpointsbehavior')->getAmountofDay('ggplus', $customer_id);
            $created = Mage::helper('rewardpointsbehavior')->getSocialCreated('ggplus', $customer_id);
            $max_points = Mage::helper('rewardpointsbehavior')->getGoogleConfig('gg_earn_limit', $store_id);
            $gg_earn = Mage::helper('rewardpointsbehavior')->getGoogleConfig('gg_earn', $store_id);
            if ($max_points > 0&&$max_points < ($total + $gg_earn)) {
                $gg_earn = $max_points - $total;
            }
            if ($gg_earn <= 0) {
                echo Mage::helper('rewardpointsbehavior')->__('You can not earn more points today.');
                return;
            }
            $current_gg = strtotime($created->getCreatedTime());
            $minium_time = Mage::helper('rewardpointsbehavior')->getGoogleConfig('minSecondsBetweenLikes', $store_id);
            if (($current_gg + $minium_time) > strtotime(now())) {
                echo Mage::helper('rewardpointsbehavior')->__('You have to wait at least %s seconds for the next +1!',$minium_time);
                return;
            }
            $gg_point = array(
                'store_id' => $store_id,
                'gg_earn' => $gg_earn,
            );
            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                        'ggplus', $customer, $gg_point, $link
                );
            } catch (Exception $exc) {
                echo $exc->getMessage();
                return;
            }

            echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for +1 via Google+!',Mage::helper('rewardpoints/point')->format($gg_earn));
        } else {
            $earnedlike = Mage::helper('rewardpointsbehavior')->getSocialEarned('ggplus', $customer_id, $link);
            $earnedRefund = $earnedlike->getFieldTotal();
            if (!$earnedRefund)
                return;
            $earned = $earnedlike->getFirstItem();
            if ($earned->getStatus() == Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED)
                return;
            $earnedlike->getFirstItem()->cancelTransaction();
            echo '';
        }
    }

}
