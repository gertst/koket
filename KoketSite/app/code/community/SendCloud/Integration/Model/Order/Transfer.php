<?php
/**
 * This class injects new buttons into sales/orders
 */
class SendCloud_Integration_Model_Order_Transfer {
    // Inject button into a single order view
    public function inject_button($event)
    {
        $block = $event->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $block->addButton('sendcloud_integration_transfer', array(
                'label' => Mage::helper('sendcloud_integration')->__('Ship with SendCloud'),
                'onclick' => "setLocation('{$block->getUrl('sendcloudintegration/transfer/index')}')",
                'class' => 'go'
            ));
        }
    }

    // Add two mass actions into the orders list
    public function inject_mass_action($event)
    {
        $block = $event->getBlock();
        if($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            // transfer action
            $block->getMassactionBlock()->addItem(
                'sendcloud_integration_masstransfer',
                array(
                    'label' => Mage::helper('sendcloud_integration')->__('Ship with SendCloud'),
                    'url'   => $block->getUrl('sendcloudintegration/transfer/mass')
                )
            );
        }
    }
}
