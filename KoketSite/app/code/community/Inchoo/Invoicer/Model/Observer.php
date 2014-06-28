<?php

class Inchoo_Invoicer_Model_Observer
{
    /**
     * Mage::dispatchEvent($this->_eventPrefix.'_save_after', $this->_getEventData());
     * protected $_eventPrefix = 'sales_order';
     * protected $_eventObject = 'order';
     * event: sales_order_save_after
     */
    public function automaticallyInvoiceShipCompleteOrder($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orders = Mage::getModel('sales/order_invoice')->getCollection()
            ->addAttributeToFilter('order_id', array('eq'=>$order->getId()));
        $orders->getSelect()->limit(1);
        if ((int)$orders->count() !== 0) {
            return $this;
        }
        //if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
        if (($order->getState() == Mage_Sales_Model_Order::STATE_NEW) &&
            ($order->getPayment()->getMethod() == "manualcardpayment")) {
            try {
                if(!$order->canInvoice()) {
                    $order->addStatusHistoryComment('Inchoo_Invoicer: Order cannot be invoiced.', false);
                    $order->save();
                }
                //START Handle Invoice
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment('Automatically INVOICED by Inchoo_Invoicer.', false);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                //END Handle Invoice
                //START Handle Shipment
                $shipment = $order->prepareShipment();
                $shipment->register();
                $shipment->getOrder()->setHasDataChanges(true); //added by gert
                $order->setIsInProcess(true);
                $order->addStatusHistoryComment('Automatically SHIPPED by Inchoo_Invoicer.', false);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                //END Handle Shipment
            } catch (Exception $e) {
                $order->addStatusHistoryComment('Inchoo_Invoicer: Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: '.$e->getMessage(), false);
                $order->save();
            }
        }
        return $this;
    }
}

?>