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
 * RewardPointsBehavior Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ENABLE = 'rewardpoints/behaviorplugin/enable';

    /**
     * get enable referfriends plugin
     * @param type $store
     * @return boolean
     */
    public function isEnable($store = null) {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE, $store) && Mage::helper('rewardpoints')->isEnable();
    }

    /**
     * get configuration
     * @param type $code
     * @param type $store
     * @return boolean
     */
    public function getBehaviorConfig($code, $store = null) {
        return true;
        //return Mage::getStoreConfig('rewardpoints/referfriendplugin/' . $code, $store);
    }

    public function getSignConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_signandnews/' . $code, $store);
    }

    public function getRateConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_rate_product/' . $code, $store);
    }

    public function getReviewConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_review_product/' . $code, $store);
    }

    public function getTagConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_tagging_product/' . $code, $store);
    }

    public function getPollConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_talk_poll/' . $code, $store);
    }

    public function getBirthdayConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_customer_birthday/' . $code, $store);
    }

    public function getFacebookConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_fb_setting/' . $code, $store);
    }

    public function getTwitterConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_tw_setting/' . $code, $store);
    }

    public function getGoogleConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_gg_setting/' . $code, $store);
    }

    public function getIcon() {
        return Mage::helper('rewardpoints/point')->getImageHtml();
    }
    /**
     * get amout poit of action now
     * @param type $action
     * @param type $customer_id
     * @return type
     */
    public function getAmountofDay($action, $customer_id, $create_time = null) {
        $date = date('Y-m-d');
        $datas = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('customer_id', $customer_id)
        ->addFieldToFilter('status', array('in'=>array(Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,Magestore_RewardPoints_Model_Transaction::STATUS_PENDING)));
        $datas->getSelect()
                ->columns(array(
                    'total' => new Zend_Db_Expr("IFNULL(SUM(main_table.point_amount), '')")));
        if($create_time != null) $datas->getSelect()->where('extra_content LIKE ?', '%create_time=' . $create_time . '%')->group('main_table.action');
        else $datas->getSelect()->where('(date(created_time) = date(?))', $date)->group('main_table.action');
        $data = $datas->getFirstItem();
        return $data->getTotal();
    }
    /**
     * get amout point of social
     * @param type $action
     * @param type $customer_id
     * @return type
     */
    public function getSocialCreated($action, $customer_id) {
        $date = date('Y-m-d');
        $datas = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('customer_id', $customer_id);
        $datas->getSelect()->where('(date(created_time) = date(?))', $date)
                ->order('main_table.created_time DESC');
        $data = $datas->getFirstItem();
        return $data;
    }
    /**
     * get point earned of customer via social
     * @param type $action
     * @param type $customer_id
     * @param type $link
     * @return type
     */
    public function getSocialEarned($action, $customer_id, $link) {
        $datas = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('extra_content', $link)
                ->addFieldToFilter('customer_id', $customer_id);
        return $datas;
    }

}