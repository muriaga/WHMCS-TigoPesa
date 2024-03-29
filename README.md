# WHMCS TigoPesa Payment Gateway
  WHMCS TigoPesa Payment Gateway API Integration (Tigo Tanzania)
  
  Payment Gateway to allow you to integrate payment solutions with the WHMCS platform
  
## Settings and Installation Procedures

Gateway Module Integration Procedures:

* Download the zip file, Upload and unzip it to your WHMCS's modules/gateways folder.

* Ensure the "tigopesa.php" file from the zip is in modules/gateways folder, the "tigopesa folder" is in modules/gateways folder too and the "tigopesa.php" file from the callback folder is put into the whmcs's modules/gateways/callback folder.

* Enable the whmcs-tigopesa module in the WHMCS admin area by going to Settings >> Apps & Integrations>> Payments >> Payments Apps >> TigoPesa Payments.

* Fill in your API Key, Secret Key, Merchant MSISDN, Merchant PIN, Account ID, AccessToken URL, Payment URL and Validate MFS URL.

* To get your API Key, Secret Key, Merchant MSISDN, Merchant PIN, Account ID, AccessToken URL, Payment URL and Validate MFS URL, Contact with [Tigopesa Tech Support](mailto:mfs.corporate@tigo.co.tz).

* Save your settings.

* When you are done testing the Tigo Pesa API code integration on sandbox environment, you will need to contact Tigo Pesa to GO live. 


* Contact Tigopesa Tech Support at mfs.corporate@tigo.co.tz for User Acceptance Testing (UAT) as the final part to enable your Account to be live online for use from the sandbox testing in their server.

* When approved to GO LIVE, you will receive Live credentials which you can replace in your settings to have your Tigo Pesa module accept Live transactions.

Note: Merchant MSISDN and Account ID are all identical.

For more information, please refer to the documentation at
[WHMCS Payment Gateways](https://developers.whmcs.com/payment-gateways).


## Features
- Automatic mobile online payments
- Send direct USSD Push notification to client's mobile
- Simplified checkout procedure, with order status updates as payments occur
- Automated  Merchant Payment Receipt Processing
- Integrates with WHMCS for TigoPesa Payments Gateway

## Module Contents ##

The structure of the Tigopesa merchant gateway module is as follows;

```s
 modules/gateways/
  |- callback/tigopesa.php
  
  |  tigopesa/templates/tigopesa_callback.tpl
  |  tigopesa/templates/tigopesa_payment.tpl
  |  tigopesa/logo.png
  |  tigopesa/createPayment.php
  |  tigopesa/processPayment.php
  |  tigopesa/whmcs.json

  |  tigopesa.php
```


## Languages used
- [PHP](https://www.php.net)
- [Smarty](https://www.smarty.net)



## Disclaimer & Copyrights
Tigo and the Tigo Pesa Logo are registered trademarks of [MIC Tanzania Public Limited Company](https://www.tigo.co.tz)


## Requirements ##

For the latest WHMCS minimum system requirements, please refer to [WHMCS System Requirements](https://docs.whmcs.com/System_Requirements)


## Useful API Reference
* [Developer Resources](https://developers.whmcs.com)
* [Hook Documentation](https://developers.whmcs.com/hooks)
* [API Documentation](https://developers.whmcs.com/api)
