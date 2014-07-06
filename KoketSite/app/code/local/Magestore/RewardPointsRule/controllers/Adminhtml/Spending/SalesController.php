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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/**
 * RewardPointsRule Adminhtml Spending Sales Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Adminhtml_Spending_SalesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_RewardPointsRule_Adminhtml_Spending_SalesController
     */
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('rewardpoints/spending')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
		return $this;
	}
    /**
     * index action
     */
	public function indexAction() {
		$this->_title($this->__('Reward Points'))
			->_title($this->__('Shopping Cart Spending Rule'));
		$this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_sales'));
        $this->renderLayout();
	}
    
    /**
     * view and edit item action, if item is new then view blank
     */
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('rewardpointsrule/spending_sales')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			$model->getConditions()->setJsFormObject('rule_conditions_fieldset');
            $model->getActions()->setJsFormObject('rule_actions_fieldset');
			Mage::register('rule_data', $model);
			
			$this->_title($this->__('Reward Points Rule'))
				->_title($this->__('Manage rule'));
			if ($model->getId()){
				$this->_title($model->getTitle());
			}else{
				$this->_title($this->__('New rule'));
			}

			$this->loadLayout();
			$this->_setActiveMenu('rewardpointsrule/rule');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule News'), Mage::helper('adminhtml')->__('Rule News'));

			$this->getLayout()->getBlock('head')
				->setCanLoadExtJs(true)
				->setCanLoadRulesJs(true);

			$this->_addContent($this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_sales_edit'))
				->_addLeft($this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_sales_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('The item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
    /*
     * new action is create new item
     */
	public function newAction() {
		$this->_forward('edit');
	}
 
    /*
     * save action is save item
     */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()){
			$model = Mage::getModel('rewardpointsrule/spending_sales')->load($this->getRequest()->getParam('id'));
			$data = $this->_filterDates($data, array('from_date', 'to_date'));
			if (!$data['from_date']) $data['from_date'] = null;
			if (!$data['to_date']) $data['to_date'] = null;
			if (isset($data['rule'])) {
                $rules = $data['rule'];
                if (isset($rules['conditions'])) {
                    $data['conditions'] = $rules['conditions'];
                }
                if (isset($rules['actions'])) {
                    $data['actions'] = $rules['actions'];
                }
                unset($data['rule']);
            }
			try {
				$model->loadPost($data)
					->setData('from_date',$data['from_date'])
					->setData('to_date',$data['to_date'])
					->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpointsrule')->__('Rule was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Unable to find the item to save'));
        $this->_redirect('*/*/');
	}
 
    /*
     * delete action is delete item
     */
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('rewardpointsrule/spending_sales');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rule was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
}
