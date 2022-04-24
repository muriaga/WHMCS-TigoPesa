<?php
    // * Author:  Medson Naftali | Technology Homesite
    // * Email:   medsonnaftal@gmail.com
    // * Website: http://technologyhomesite.com

    require("../../../init.php");
    include("../../../includes/gatewayfunctions.php");
    include("../../../invoicefunctions.php");
    
    define("CLIENTAREA",true);
    define("FORCESSL",true); // Uncomment to force the page to use https://
    
    global $CONFIG;
    $gatewaymodule = "tigopesa";
    
    $gateway = getGatewayVariables($gatewaymodule);
    $systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'].'/' : $CONFIG['SystemURL'].'/';
    $baseURL = $gateway['basedomainurl'] ? $gateway['basedomainurl'] : $systemurl;
    
    // Checks gateway module is active before accepting callback

    if (!$gateway["type"]) die ("Tigopesa Module Not Activate");
    
    // Customer Phone Number
    $phone = $_POST['phone'];
    $new_phone = preg_replace('/(?<=\d)\s+(?=\d)/', '', $phone);
    $country_code = $_POST['country-calling-code-phone'];
    $phoneNumber = $country_code.$new_phone;
    
    // Gateway Configurations
    $api_key = $gateway['apiKey'];
    $secret_key = $gateway['secretKey'];
    $customerMSISDN = $gateway['customerMSISDN'];
    $customerPIN = $gateway['customerPIN'];
    $accountID = $gateway['accountID'];
    
    // Order Configuration
    $data = base64_decode($_POST["data"]);
    $data = unserialize($data);
    
    // Subscriber
    $account = $phoneNumber;
    $firstname = $data['clientdetails']['firstname'];
    $lastname = $data['clientdetails']['lastname'];
    $email = $data['clientdetails']['email'];
    $invoiceid = $data['invoiceid'];
    
    $redirect_url = $baseURL . "modules/gateways/callback/tigopesa.php";
    
    // Origin Payment
    $amount = $data['amount'];
    $currencyCode = $data['currency'];
   
    $refecence_id = $invoiceid. "DU". generate_random_char();
    
    // Function to Generate Reference ID.
    function generate_random_char($data = null) {
        
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    $payUrl = $gateway['paymentURL'];
    
    $payload = array (
        "MasterMerchant" => array(
            "account" => $customerMSISDN,
            "pin" => $customerPIN,
            "id" => $accountID
        ),
        "Subscriber" => array(
            "account" => $phoneNumber,
            "countryCode" => "255",
            "country" => "TZA",
            "firstName" => $firstname,
            "lastName" => $lastname,
            "emailId" => $email
        ),
        "redirectUri" => $redirect_url,
        "language" => "swa",
        "terminalId" => "",
        "originPayment" => array(
            "amount" => $amount,
            "currencyCode" => $currencyCode,
            "tax" => "0.00",
            "fee" => "0.00"
        ),
        "exchangeRate" => "1",
        "LocalPayment" => array(
            "amount" => $amount,
            "currencyCode" => $currencyCode,
        ),
        "transactionRefId" => $refecence_id,
    );
    
    $payload = json_encode($payload);
    
    $accessToken = base64_decode($_POST['accessToken']);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $payUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "accessToken: $accessToken",
        ),
    ));
    
    $result = curl_exec($ch);
    $results = json_decode($result);
    $redirectUrl = $results->redirectUrl;
    curl_close($ch);
    
    // Redirect to tigopesa Secure Payment URL
    echo '<script type="text/javascript">
          window.location = "'. $redirectUrl.'"
     </script>';
?>