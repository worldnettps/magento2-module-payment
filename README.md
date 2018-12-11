===================================================================================
 WorldNetTPS Magento 2.x Payment Page module V1.5 19-11-2018
===================================================================================
Contributors: WorldNetTPS
Link: https://worldnettps.com/
Tags: payment
Tested up to: 2.1.8
Stable tag: 1.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html


================================ Installation ====================================

This module has to be installed after the Magento installation process.

There is one folder ("WorldnetTPS") within the WorldNetTPS Payment Page Module
This folder should be placed into the "app > code" folder of the Magento root folder.

The store should be configured to be accessed through a direct domain/subdomain, not in a folder, 
otherwise you will encounter problems when using the plugin.

From CLI, chmod 777 on that folder recursively. Upgrade and recompile magento, deploy static content 
and clean the cache using the following commands:

    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy;
    php bin/magento cache:clean;
    
Once the above commands are successfully completed, chmod 777 on the WorldnetTPS plugin folder recursively once again.
 
You will then need to go to the admin section of your Magento installation:

1) Magento Admin -> Stores -> Configuration -> Sales -> Payment Methods
2) You should then see "WorldnetTPS Payment Gateway" appear as a payment method (if not see below). 
3) Click on this and select Enabled = "Yes".
4) Enter the Gateway, Currency, Terminal ID and your "Shared Secret". (These details were supplied by WorldNetTPS.)
5) Click "Save Config" in the top right corner.


====================== How to Perform Refund =======================================
To perform a refund on a transaction,
Go to: Magento Admin->Sales->Invoices->View->Credit Memo...->Refund


=========================== TroubleShooting =========================================
If the module does not appear on the "Payment Methods" page you may have to clear/refresh Magento's cache.
To do this go to System -> Cache Management and click the "Flush Magento Cache" button on the top right.

============================= CHANGELOG =============================================

-----------------------------------------------------------------------------
V1.5 - $_GET and $_POST replaced with getRequest methods

-----------------------------------------------------------------------------
V1.4 - SecureCard implemented
     - Subscription functionality available

-----------------------------------------------------------------------------
V1.3 - Phase 1 of a new plugin version developed and tested with
       Magento 2.1.8
     - Plugin supports both HPP and XML payments, Void & Refund, AVS & CVV validation, 
       Threat Metrix integration, custom fields integration.
     - Unlike V1.2, the payment gateway is branded for different clients.

-----------------------------------------------------------------------------
V1.2 - Added Payzone,GlobalOnePay,AnywhereCommerce,CT Payments,PayConex Plus,
       CashFlows.
       Added Swedish Krona, Danish Krone,Australian Dollar,Canadian Dollar 
       as currency options.
       
-----------------------------------------------------------------------------
V1.1 - Added config option to use either Store Base currency or Cart Display
       Currency as the checkout currency.
     - Disabled refunds if using the Cart Display Currency as they went in
       the base currency (wrong amount).

-----------------------------------------------------------------------------
V1.0 - Tested Magento 1.4 HPP plug-in version 1.7 in Magento 1.6.2 successfully

-----------------------------------------------------------------------------
============================= Contact Email ==============================================
For any Further Queries or Doubts Please contact us via below e-mail
Email -: support@worldnettps.com

==========================================================================================

