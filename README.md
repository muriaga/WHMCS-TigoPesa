# WHMCS TigoPesa Payment Gateway
  WHMCS TigoPesa Payment Gateway API Integration (Tigo Tanzania)
  
  Payment Gateway to allow you to integrate payment solutions with the WHMCS platform.
  
## Settings and Installation Procedures

Gateway Module Integration Procedures:

* Download the zip file, Upload and unzip it to your WHMCS's modules/gateways folder.

* Ensure the "tigopesa.php" file fromthe zip is in modules/gateways folder, the "tigopesa folder" is in modules/gateways folder too and the "tigopesa.php" file from the callback folder is put into the whmcs's modules/gateways/callback folder.

* Enable the whmcs-tigopesa module in the WHMCS admin area by going to Settings >> Apps & Integrations>> Payments >> Payments Apps >> TigoPesa Payments.

* Fill in your API Key, Secret Key, Merchant MSISDN, Merchant PIN, Account ID, AccessToken URL, Payment URL and Validate MFS URL.

* To get your PIN, Secret Key, API Key, Access Token URL, Validate MFS URL, Payment URL and , Contact with Tigopesa Tech Support.

* Save your settings.

* When you are done testing the Tigo Pesa API code integration on sandbox environment, you will need to contact Tigo Pesa to GO live. 


* Contact Tigopesa Tech Support at mfs.corporate@tigo.co.tz for User Acceptance Testing (UAT) as the final part to enable your Account to be live online for use from the sandbox testing in their server.

* When approved to GO LIVE, you will receive Live credentials which you can replace in your code to have your Tigo Pesa API code accept Live transactions.

Note: Merchant MSISDN and Account ID are all identical.

For more information, please refer to the documentation at:
https://developers.whmcs.com/payment-gateways/

## Recommended Module Content ##

The recommended structure of the Tigopesa merchant gateway module is as follows.

```s
 modules/gateways/
  |- callback/tigopesa.php
  |  tigopesa
  |  tigopesa.php
```

## Minimum Requirements ##

For the latest WHMCS minimum system requirements, please refer to
https://docs.whmcs.com/System_Requirements


## Useful Resources
* [Developer Resources](https://developers.whmcs.com/)
* [Hook Documentation](https://developers.whmcs.com/hooks/)
* [API Documentation](https://developers.whmcs.com/api/)



