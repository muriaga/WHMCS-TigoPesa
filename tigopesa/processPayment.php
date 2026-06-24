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
$baseURL = !empty($gateway['basedomainurl']) ? $gateway['basedomainurl'] : $systemurl;

// Checks gateway module is active before accepting callback
if (empty($gateway["type"])) {
    die ("Tigopesa Module Not Active");
}

// Read order POST param (base64-encoded JSON)
$orderEncoded = $_POST['order'] ?? '';
if (empty($orderEncoded)) {
    die('Missing order data');
}

$orderJson = base64_decode($orderEncoded, true);
if ($orderJson === false) {
    die('Invalid order payload encoding');
}

$orderDetails = json_decode($orderJson, true);
if (!is_array($orderDetails)) {
    die('Invalid order payload format');
}

$invoiceid = checkCbInvoiceID($orderDetails['invoiceid'], $gateway["name"]);
if (!$invoiceid) {
    die ("Invalid order user");
}

$api_key = $gateway['apiKey'] ?? '';
$secret_key = $gateway['secretKey'] ?? '';

$amount = $orderDetails['amount'];
$amount = number_format($amount, 2); //format amount to 2 decimal places
$invoiceid = $orderDetails['invoiceid'];

$access_token_url = $gateway['access_token_url'] ?? '';
if (empty($access_token_url)) {
    die('Access token URL not configured');
}

$data = [
  'client_id' => $api_key,
  'client_secret' => $secret_key
];

$ch = curl_init($access_token_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded'
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$responseRaw = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($responseRaw === false || !empty($curlErr)) {
    die('Error requesting access token: ' . $curlErr);
}

$response = json_decode($responseRaw);
if (!is_object($response) || empty($response->accessToken)) {
    die('Invalid access token response');
}

$accessToken = $response->accessToken;

// Encode & set the AccessToken in session (base64)
session_start();
$_SESSION["accessToken"] = base64_encode((string)$accessToken);

$createPaymentUrl = ($gateway['basedomainurl'] ?? $systemurl) . 'modules/gateways/tigopesa/createPayment.php';

$ca = new WHMCS_ClientArea();
$ca->setPageTitle("Secure Payments | TigoPesa");
$ca->addToBreadCrumb('index.php', $CONFIG['CompanyName'] ?? 'Home');
$ca->initPage();
$ca->assign('amount', $amount);
$ca->assign('data', base64_encode(json_encode($orderDetails))); // ensure template posts base64-encoded JSON
$ca->assign('invoiceid', $invoiceid);
$ca->assign('createPaymentUrl', $createPaymentUrl);
$ca->assign('accessToken', base64_encode((string)$accessToken)); // template may include it, but we prefer session
$ca->setTemplate('tigopesa_payment');

$ca->output();

?>