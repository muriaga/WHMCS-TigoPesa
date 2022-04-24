<?php


    require("../../../init.php");
    include("../../../includes/gatewayfunctions.php");
    include("../../../invoicefunctions.php");
    
    define("CLIENTAREA", true);
    define("FORCESSL", true);  // Force https
    
    global $CONFIG;
    
    $gatewaymodule = "tigopesa";
    $gateway = getGatewayVariables($gatewaymodule);
    $systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'].'/' : $CONFIG['SystemURL'].'/';
    $baseURL = $gateway['basedomainurl'] ? $gateway['basedomainurl'] : $systemurl;
    
    // Checks gateway module is active before accepting callback
    if (!$gateway["type"]) die ("Tigopesa Module Not Activate");
    
    $order = base64_decode($_POST["order"]);
    $orderDetails = unserialize($order);
    
    $invoiceid = checkCbInvoiceID($orderDetails['invoiceid'], $gateway["name"]);
    if (!$invoiceid) die ("Invalid order user");
    
    $api_key = $gateway['apiKey'];
    $secret_key = $gateway['secretKey'];
    
    $amount = $orderDetails['amount'];
    $amount = number_format($amount, 2); //format amount to 2 decimal places
    $invoiceid = $orderDetails['invoiceid'];
    
    $access_token_url = $gateway['access_token_url'];
    
    $data = [
      'client_id' =>$api_key,
      'client_secret' => $secret_key
    ];

    $ch = curl_init($access_token_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded'
    ));
    curl_setopt($ch, CURLOPT_URL, $access_token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    $response = json_decode($response);

    $accessToken = $response->accessToken;
    
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    if (curl_errno($ch)) {
        return 'Error:' . curl_error($ch);
    }
    
    curl_close($ch);

    $baseUrl = ($gatewayConfigs['basedomainurl']) ? $gatewayConfigs['basedomainurl'] : $systemurl; //.'modules/gateways/tigopesa/processPayment.php';
    
    $createPaymentUrl = $baseUrl . 'modules/gateways/tigopesa/createPayment.php';
    
    $data = $_POST['order'];
    
    // Encode & Set the AccessToken in Session
    $accessToken = base64_encode($accessToken);
    session_start();
    $_SESSION["accessToken"] = $accessToken;
    
    $ca = new WHMCS_ClientArea();
    $ca->setPageTitle("Secure Payments | TigoPesa");
    $ca->addToBreadCrumb('index.php', $orderDetails['globalsystemname']);
    $ca->initPage();
    $ca->assign('amount', $amount);
    $ca->assign('data', $data);
    $ca->assign('invoiceid', $invoiceid);
    $ca->assign('createPaymentUrl', $createPaymentUrl);
    $ca->assign('accessToken', $accessToken);
    $ca->setTemplate('tigopesa_payment'); 
    
    $ca->output();
    
?>