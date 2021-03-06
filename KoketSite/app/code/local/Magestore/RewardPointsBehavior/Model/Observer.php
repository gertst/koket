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
 * RewardPointsBehavior Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Model_Observer {

    /**
     *  process customer register succucess for mageno below 1.7xx
     * @param type $observer
     * @return type
     */
    public function customerRegisterSuccessForLow($observer) {
        if (!Mage::helper('rewardpointsbehavior')->isEnable()) {
            return;
        }
//        if (version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
//            return;
//        }
        $customer_reg = $observer->getCustomer();
        $customer_reg->getCreatedAt();
        $created_at = date('Y-m-d', strtotime($customer_reg->getCreatedAt()));
        $current_date = date('Y-m-d');
        if(strtotime($current_date)!=  strtotime($created_at)){
            return;
        }
        $customer = Mage::getModel('customer/customer')->load($customer_reg->getId());
        if (!$customer->getId() || Mage::app()->getRequest()->getActionName() == 'editPost')
            return;
        $registed_points = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', 'registed')
                ->addFieldToFilter('customer_id', $customer->getId())
                ->getFieldTotal();
        if ($registed_points)
            return;
        try {
            Mage::helper('rewardpoints/action')->addTransaction(
                    'registed', $customer, $customer_reg, array(
                'notice' => Mage::helper('rewardpointsbehavior')->__('Register Success'),
                'customer_extra_content' => array(
                    'registed' => 1,
                ),
                    )
            );
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
//        return $this;
    }

    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_RewardPointsBehavior_Model_Observer
     */
    public function newsletterSubscriber($observer) {
        if (!Mage::helper('rewardpointsbehavior')->isEnable()) {
            return $this;
        }
        $subscriber = $observer->getEvent()->getSubscriber();
        if (!$subscriber->isSubscribed())
            return $this;
        $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());

        if (!$customer->getId())
            return $this;
        $news_data = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', 'newsletter')
                ->addFieldToFilter('customer_id', $customer->getId())
                ->getFirstItem();
        if ($news_data->getId())
            return $this;
        try {
            Mage::helper('rewardpoints/action')->addTransaction(
                    'newsletter', $customer, $subscriber, array(
                'notice' => Mage::helper('rewardpointsbehavior')->__('Signup newsletter'),
                'customer_extra_content' => array(
                    'signup_newsletter' => 1,
                ),
                    )
            );
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }


        return $this;
    }

    /**
     * proccess event customer login success
     */
    /* public function customerRegisterSuccess($observer) {
        $customer_reg = $observer->getCustomer();
        $customer = Mage::getModel('customer/customer')->load($customer_reg->getId());
        if (!$customer->getId())
            return $this;
		$registed_points = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', 'registed')
                ->addFieldToFilter('customer_id', $customer->getId())
                ->getFieldTotal();
        if ($registed_points)
            return;
        try {
            Mage::helper('rewardpoints/action')->addTransaction(
                    'registed', $customer, $customer_reg, array(
                'notice' => Mage::helper('rewardpointsbehavior')->__('Register Success'),
                'customer_extra_content' => array(
                    'registed' => 1,
                ),
                    )
            );
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
        return $this;
    }
	*/
    public function controllerActionPredispatch($observer) {
        $action = $observer->getEvent()->getControllerAction();
        return $this;
    }

    /**
     * proccess review save affer
     * @param type $observer
     * @return \Magestore_RewardPointsBehavior_Model_Observer
     */
    public function reviewSaveAfter($observer) {

        if (!Mage::helper('rewardpointsbehavior')->isEnable()) {
            return $this;
        }

        $review = $observer->getDataObject();
        if ($review->getStatusId() != Mage_Review_Model_Review::STATUS_APPROVED)
            return $this;

        $customerId = $review->getCustomerId();
        if (!$customerId)
            return $this;

        $productId = $review->getEntityPkValue();
        $product = Mage::getModel('catalog/product')->load($productId);

        $customer = Mage::getModel('customer/customer')->load($customerId);
        $condition = '%product_id=' . $productId . '%';
        if (!$this->_getTransbyProductIds($customerId, $condition, 'review')) {
            Mage::helper('rewardpoints/action')->addTransaction('review', $customer, $review, array(
                'notice' => Mage::helper('rewardpointsbehavior')->__('Review product'),
                'extra_content' => array(
                    'product_id' => $product->getId(),
                    'product_name' => $product->getName(),
                    'create_time' => date('Y-m-d', strtotime($review->getCreatedAt()))
                ),
                'product_name' => $product->getName(),
            ));
        }
        if (!$this->_getTransbyProductIds($customerId, $condition, 'rate')) {
            Mage::helper('rewardpoints/action')->addTransaction('rate', $customer, $review, array(
                'notice' => Mage::helper('rewardpointsbehavior')->__('Rate a product'),
                'extra_content' => array(
                    'product_id' => $product->getId(),
                    'product_name' => $product->getName(),
                ),
                'product_name' => $product->getName(),
            ));
        }
        return $this;
    }

    protected function _getTransbyProductIds($customerId, $condition, $action) {
        $review_data = Mage::getModel('rewardpoints/transaction')->getCollection();
        $review_data->getSelect()->where('action = ?', $action)
                ->where('customer_id = ?', $customerId)
                ->where('extra_content LIKE ?', $condition);
        return $review_data->getFirstItem()->getId();
    }

    /**
     * process event model save after 
     * @param type $observer
     * @return \Magestore_RewardPointsBehavior_Model_Observer
     */
    public function modelSaveAfter($observer) {
        if (!Mage::helper('rewardpointsbehavior')->isEnable()) {
            return $this;
        }
        $tagRelation = $observer->getObject();
        if ($tagRelation instanceof Mage_Tag_Model_Tag_Relation) {
            $tag = Mage::getModel('tag/tag')->load($tagRelation->getTagId());
            if ($tag->getStatus() == Mage_Tag_Model_Tag::STATUS_APPROVED) {
                $this->_addTagProductPoint($tagRelation);
                return $this;
            }
        }
        if (($pollVote = $observer->getObject()) instanceof Mage_Poll_Model_Poll_Vote) {

            $customerId = $pollVote->getCustomerId();
            if (!$customerId)
                return $this;
            $customer = Mage::getModel('customer/customer')->load($customerId);
            Mage::helper('rewardpoints/action')->addTransaction('poll', $customer, $pollVote, array(
                'notice' => Mage::helper('rewardpointsbehavior')->__('Participate in poll'),
                'poll_id' => $pollVote->getId()
            ));
            return $this;
        }
    }

    public function adminTagSave($observer) {
        $action = $observer->getEvent()->getControllerAction();
        $tagId = $action->getRequest()->getParam('tag_id');

        $storeId = $action->getRequest()->getParam('store');
        if (!Mage::helper('rewardpointsbehavior')->isEnable()) {
            return $this;
        }
        $tagStatus = $action->getRequest()->getParam('tag_status');
        if ($tagStatus != Mage_Tag_Model_Tag::STATUS_APPROVED)
            return $this;
        $tagRelationCollection = Mage::getModel('tag/tag')->getCollection()->joinRel();
        $tagRelationCollection->getSelect()
                ->where('main_table.tag_id = ?', $tagId)
                ->where('relation.store_id = ?', $storeId)
                ->where('relation.active = 1');
        foreach ($tagRelationCollection->getData() as $tag) {

            $tagRelation = Mage::getModel('tag/tag_relation')->load($tag['tag_relation_id']);
            $this->_addTagProductPoint($tagRelation);
        }
        return $this;
    }

    /**
     * 
     * @param type $observer
     * @return \Magestore_RewardPointsBehavior_Model_Observer
     */
    public function adminTagmassStatus($observer) {
        $action = $observer->getEvent()->getControllerAction();

        $tagStatus = $action->getRequest()->getParam('status');
        if ($tagStatus != Mage_Tag_Model_Tag::STATUS_APPROVED)
            return $this;
        $tagIds = $action->getRequest()->getParam('tag');
        foreach ($tagIds as $tagId) {
            $tag_temp = Mage::getModel('tag/tag')->load($tagId);
            $storeId = $tag_temp->getFirstStoreId();
            if ($tag_temp->getStatus() != Mage_Tag_Model_Tag::STATUS_APPROVED && Mage::helper('rewardpointsbehavior')->isEnable()) {
                $tagRelationCollection = Mage::getModel('tag/tag')->getCollection()->joinRel();
                $tagRelationCollection->getSelect()
                        ->where('main_table.tag_id = ?', $tagId)
                        ->where('relation.store_id = ?', $storeId)
                        ->where('relation.active = 1');
                foreach ($tagRelationCollection->getData() as $tag) {
                    $tagRelation = Mage::getModel('tag/tag_relation')->load($tag['tag_relation_id']);
                    $this->_addTagProductPoint($tagRelation);
                }
            }
        }
        return $this;
    }

    /**
     * 
     * @param type $tagRelation
     * @return \Magestore_RewardPointsBehavior_Model_Observer
     */
    protected function _addTagProductPoint($tagRelation) {

        $customerId = $tagRelation->getCustomerId() ? $tagRelation->getCustomerId() : $tagRelation->getFirstCustomerId();
        if (!$customerId)
            return $this;

        $productId = $tagRelation->getProductId();
        $product = Mage::getModel('catalog/product')->load($productId);

        $customer = Mage::getModel('customer/customer')->load($customerId);

        Mage::helper('rewardpoints/action')->addTransaction('tag', $customer, $tagRelation, array(
            'notice' => Mage::helper('rewardpointsbehavior')->__('Tag product'),
            'extra_content' => array(
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
            ),
            'product_name' => $product->getName(),
        ));
        return $this;
    }

    /**
     * 
     * @param type $observer
     * @return \Magestore_RewardPointsBehavior_Model_Observer
     */
    function checkCustomerBirthdays($observer) {
        $customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*');
        $today = Mage::getModel('core/date')->gmtDate('md', time());
        $thisyear = Mage::getModel('core/date')->gmtDate('y', time());
        foreach ($customers as $customer) {
            if (!$customer->getDob())
                continue;
            if (Mage::getModel('core/date')->gmtDate('md', $customer->getDob()) == $today) {
                $bd_data = Mage::getResourceModel('rewardpoints/transaction_collection')
                        ->addFieldToFilter('action', 'birthday')
                        ->addFieldToFilter('customer_id', $customer->getId());
                $bd_data->setOrder('created_time', 'DESC');
                $bd_data = $bd_data->getFirstItem();
                if ($bd_data->getId())
                    if (Mage::getModel('core/date')->gmtDate('y', $bd_data->getCreatedTime()) == $thisyear)
                        continue;
                $store = $customer->getStoreId();
                Mage::helper('rewardpoints/action')->addTransaction('birthday', $customer, $store, array(
                    'notice' => Mage::helper('rewardpointsbehavior')->__('Birthday')
                ));
            }
        }
        return $this;
    }

}

