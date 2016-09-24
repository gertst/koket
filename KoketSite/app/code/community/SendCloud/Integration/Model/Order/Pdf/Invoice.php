<?php
/**
 * This class adds a barcode to the default shipment pdfs
 */
class SendCloud_Integration_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
    public function getPdf($shipments = array())
    {
        $pdf = parent::getPdf($shipments);
        $hasScanGo = Mage::getStoreConfig('sendcloudintegration/configuration/scanandgo');
        if(!$hasScanGo) return $pdf;

        $pdf = parent::getPdf($shipments);
        $i = 0;
        foreach ($shipments as $shipment) {
            $page = $pdf->pages[$i];
            $order = $shipment->getOrder();
            $parcel_id = $order->getData('sendcloud_parcel_id');
            if (!empty($parcel_id)) {
                $barcodeText = $this->getBarcodeFormattedId("p".$parcel_id);

                $page->drawText('SendCloud Barcode', 420, 70);

                $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
                $page->setFont(Zend_Pdf_Font::fontWithPath(Mage::getBaseDir('lib') .'/SendCloud/code128.ttf'), 23);
                $page->drawText($barcodeText, 420, 30, 'CP1252');
            }

            $i++;
        }
        return $pdf;
    }

    protected function convertToBarcodeString($toBarcodeString)
    {
        $str = $toBarcodeString;
        $barcode_data = str_replace(' ', chr(128), $str);

        $checksum = 104; // must include START B code 128 value (104) in checksum
        for($i=0;$i<strlen($str);$i++) {
            $code128 = '';
            if (ord($barcode_data{$i}) == 128) {
                    $code128 = 0;
            } elseif (ord($barcode_data{$i}) >= 32 && ord($barcode_data{$i}) <= 126) {
                    $code128 = ord($barcode_data{$i}) - 32;
            } elseif (ord($barcode_data{$i}) >= 126) {
                    $code128 = ord($barcode_data{$i}) - 50;
            }
            $checksum_position = $code128 * ($i + 1);
            $checksum += $checksum_position;
        }
        $check_digit_value = $checksum % 103;
        $check_digit_ascii = '';
        if ($check_digit_value <= 94) {
            $check_digit_ascii = $check_digit_value + 32;
        } elseif ($check_digit_value > 94) {
            $check_digit_ascii = $check_digit_value + 50;
        }
        $barcode_str = chr(154) . $barcode_data . chr($check_digit_ascii) . chr(156);

        return $barcode_str;

    }

  protected function getBarcodeFormattedId($parcel_id) {
    $barcodeText = '';
    $barcodeText = $parcel_id;


    $count_characters = 8 - strlen($parcel_id);

    for ($x = 0; $x < $count_characters; $x++) {
      $barcodeText = '0' . $barcodeText;
    }

    return $this->convertToBarcodeString($barcodeText);
  }

}
