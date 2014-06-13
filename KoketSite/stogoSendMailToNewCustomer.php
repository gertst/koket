<?php
ob_start();
error_reporting(E_ALL | E_STRICT);
$mageFilename = 'app/Mage.php';
if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo "Send-mail-to-newcustomers: error:" . $mageFilename." was not found";
    }
    exit;
}

require_once $mageFilename;
Varien_Profiler::enable();
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('nl');


$passwordLength = 10;

if (isset($_GET["custid"])) {
    $customer_id = $_GET['custid'];
} else {
    echo ("Send-mail-to-newcustomers: error: no customer id");
    exit;
}
$templateId = 1; //template in Transactional Emails called "New backend account informing password"

$customer = Mage::getModel('customer/customer')->load($customer_id);
$customer->load($customer->getId());

if(!empty($customer))
{

    $points = Mage::getModel('rewardpoints/customer')->load($customer_id, 'customer_id')->getPointBalance();
    $reduction = $points / 100;
    $reduction = number_format($reduction, 2, ',', ' ');

    $orderCollection = Mage::getModel("sales/order")->getCollection()->addFieldToFilter('customer_id', array('eq' => array($customer_id)));
    //only reset pw and send mail when new customer (= has one order)

    if (count($orderCollection) == 1) {

        $newPassword = $customer->generatePassword($passwordLength);
        $customer->setPassword($newPassword)->save();
        $mailTemplate = Mage::getModel('core/email_template');

        $translate  = Mage::getSingleton('core/translate');


        $template_collection =  $mailTemplate->load($templateId);
        $template_data = $template_collection->getData();
        if(!empty($template_data))
        {
            $templateId = $template_data['template_id'];
            $mailSubject = $template_data['template_subject'];

            //fetch sender data from Adminend > System > Configuration > Store Email Addresses > General Contact
            $from_email = Mage::getStoreConfig('trans_email/ident_general/email'); //fetch sender email
            $from_name = Mage::getStoreConfig('trans_email/ident_general/name'); //fetch sender name

            $sender = array('name'  => $from_name,
                'email' => $from_email);

            $vars = array(  'customer'=>$customer,
                            "newpass"=>$newPassword,
                            "points"=>$points,
                            "reduction"=>$reduction); //for replacing the variables in email with data
            /*This is optional*/
            $storeId = Mage::app()->getStore()->getId();
            $model = $mailTemplate->setReplyTo($sender['email'])->setTemplateSubject($mailSubject);
            $email = $customer->getEmail();
            $name = $customer->getName();
            $model->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
            if (!$mailTemplate->getSentSuccess()) {
                throw new Exception();
            }
            $translate->setTranslateInline(true);
            echo("Welcome mail sent to new customer " . $email . ". Password: " . $newPassword);

            registerToNewsletter($email);

        } else {
            echo("Send-mail-to-newcustomers: error: empty email template");
        }
    } else {
        echo("Not a new client: No welcome mail sent. Order count: " . count($orderCollection));
    }

} else {
    echo("Send-mail-to-newcustomers: error: empty customer");
}

function registerToNewsletter($email) {
    //Mage::log('_autoSubscribe: '.$email);
    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
    if($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED &&
        $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
        $subscriber->setImportMode(true)->subscribe($email);
    }
}

?>