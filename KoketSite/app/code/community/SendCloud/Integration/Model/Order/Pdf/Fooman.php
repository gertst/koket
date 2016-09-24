<?php
/**
 * This class replaces the barcode from Fooman by our own.
 */
class SendCloud_Integration_Model_Order_Pdf_Fooman extends Fooman_PdfCustomiser_Model_Mypdf {
    public function printHeader(Fooman_PdfCustomiser_Helper_Pdf $helper, $title, $incrementId = false, $hideLogo)
    {
        // check for automatic transfer
        $hasScanGo = Mage::getStoreConfig('sendcloudintegration/configuration/scanandgo');
        if(!$hasScanGo) return parent::printHeader($helper, $title, false, $hideLogo);

        $style = array('text' => false);
        if ($incrementId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            $parcel_id = $order->getData('sendcloud_parcel_id');
            if (!empty($parcel_id)) {
                $this->write1DBarcode(
                  "p".$parcel_id,
                  'C128',
                  $helper->getPdfMargins('sides'),
                  5,
                  50,
                  15,
                  '',
                  $style
                  );

                $this->SetXY($helper->getPdfMargins('sides'), $helper->getPdfMargins('top'));
            }
        }

        return parent::printHeader($helper, $title, false, $hideLogo);
    }
}
