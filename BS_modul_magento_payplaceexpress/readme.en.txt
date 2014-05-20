Module: "payplace.express" payment module

Version: 1.1.0

Description: Payment module for conducting payments via "payplace.express" system

Tested with Shop-System: Magento ver. 1.7.0.2, 1.8.0.0

1	Installation
1.1 Installation of Plug-Ins
	a) Upload the installation file "magento_payplace.express_1.1.0.zip" to a temporrary directory (/tmp) on the shop server.
	b) Unpack the file into the /tmp directory (unzip magento_payplace.express_1.1.0.zip).
	c) Copy the content of directory  "copy_this" into the root directory of the shop.
	d) Ensure that the new files and directories are readable/executable by the shop (access rights 755).

2 Configuration
2.1 Configuration in the Frontoffice
	a) In a browser, open the login page of the Frontoffice and log in.
	b) Open the view "Configuration>Form Service" and enter the following value into the field "Shop notification URL":
	   <Shop-URL>/index.php/Payplace_payment/Notification
	c) Enter the MAC Key you've chosen into the fields "MAC Key" and "MAC Key - Repetition".
	   (You need to use the same MAC Key for configuring the Plug-In in the shop system)
	e) Click the button "Save".

2.2 Configuration of the Plug-In within the Shop system
	a) In a browser, open the administration view of the shop and log in as administrator.
	b) Open the view "Configuration General" by clicking in the menu "System>Configuration".
	   Scroll down the window and click on "Payment Methods" to get the list of all payment methods available.
	c) Click on "payplace.express Base".
	d) Enter your Merchant Login into the field "Merchant Login"
	e) Enter the MAC Key you've chosen into the field "MAC Key".
	   (The MAC key must be the same as the one you used in the Frontoffice)
	f) Click the button "Save Config".


3.1 update von 1.0.x auf 1.1.0
	a) run the installation steps 1.1 c and d
	b) refresh the cache of the shop

Changes:
Version 1.1.0
-new payment method "direct debit SEPA"
-update giropay payment for sepa

Version 1.0.3
	Bugfix: "payplace.express" giropy payment method can now be disabled.

Version 1.0.2
	Bugfix: The order status is now set to 'Complete' after a successful payment.

Version 1.0.1
	Bugfix: The parameter "payment_options" is configurable now and will be transmitted to the payment gateway "payplace.express".
