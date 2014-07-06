<?php
/**
 * User: Gert
 * Date: 9/04/14
 * Time: 22:07
  */

class Stogo_CreateOrder_Model_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
    /**
     * Create new order
     *
     * @return Mage_Sales_Model_Order
     */
    public function createOrder()
    {
        Mage::throwException(Mage::helper('adminhtml')->__('Gert was here.'));
        //parent::createOrder();
    }
}