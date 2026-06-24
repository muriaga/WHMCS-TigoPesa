<?php

require("../../../init.php");
include("../../../includes/gatewayfunctions.php");
include("../../../invoicefunctions.php");

define("CLIENTAREA",true);
define("FORCESSL",true); // Force https

global $CONFIG;
$gatewaymodule = "tigopesa";

$gateway = getGatewayVariables($gatewaymodule);
if (empty($gateway["type"])) {
    die ("Tigopesa Module Not Active");
}

// Prefer order param name 'order' (base64 JSON). Support legacy 'data' if present.
$encoded = $_POST['order'] ?? ($_POST['data'] ?? '');
if (empty($encoded)) {
    die('Missing order payload');
}

$json = base64_decode($encoded, true);
if ($json === false) {
    die('Invalid order payload encoding');
}

$order = json_decode($json, true);
if (!is_array($order)) {
    die('Invalid order payload');
}

$systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'].'/' : $CONFIG['SystemURL'].'/';
$baseURL = $gateway['basedomainurl'] ? $gateway['basedomainurl'] : $systemurl;

// Customer Phone
$phone = $_POST['phone'] ?? '';
$new_phone = preg_replace('/(?<=\d)\s+(?=\d)/', '', $phone);
$country_code = $_POST['country-calling-code-phone'] ?? '';
$phoneNumber = $country_code . $new_phone;

// Gateway Configurations
$customerMSISDN = $gateway['customerMSISDN'] ?? '';
$customerPIN = $gateway['customerPIN'] ?? '';
$accountID = $gateway['accountID'] ?? '';

// Order config
$firstname = $order['clientdetails']['firstname'] ?? '';
$lastname = $order['clientdetails']['lastname'] ?? '';
$email = $order['clientdetails']['email'] ?? '';
$invoiceid = $order['invoiceid'] ?? '';
$amount = $order['amount'] ?? 0;
$currencyCode = $order['currency'] ?? '';

$reference_id = $invoiceid . "DU" . bin2hex(random_bytes(8)); // shorter unique suffix

$payload = [
    "MasterMerchant" => [
        "account" => $customerMSISDN,
        "pin" => $customerPIN,
        "id" => $accountID
    ],
    "Subscriber" => [
        "account" => $phoneNumber,
        "countryCode" => "255",
        "country" => "TZA",
        "firstName" => $firstname,
        "lastName" => $lastname,
        "emailId" => $email
    ],
    "redirectUri" => $baseURL . "modules/gateways/callback/tigopesa.php",
    "language" => "swa",
    "terminalId" => "",
    "originPayment" => [
        "amount" => $amount,
        "currencyCode" => $currencyCode,
        "tax" => "0.00",
        "fee" => "0.00"
    ],
    "exchangeRate" => "1",
    "LocalPayment" => [
        "amount" => $amount,
        "currencyCode" => $currencyCode,
    ],
    "transactionRefId" => $reference_id,
];

$payloadJson = json_encode($payload);

// Use accessToken from session (set in processPayment.php)
session_start();
$accessTokenEncoded = $_SESSION['accessToken'] ?? '';
if (empty($accessTokenEncoded)) {
    die('Access token not found in session');
}
$accessToken = base64_decode($accessTokenEncoded, true);
if ($accessToken === false) {
    die('Invalid access token encoding');
}

$payUrl = $gateway['paymentURL'] ?? '';
if (empty($payUrl)) {
    die('Payment URL not configured');
}

$ch = curl_init($payUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "accessToken: " . $accessToken,
));

$result = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($result === false || !empty($curlErr)) {
    die('Error contacting payment provider: ' . $curlErr);
}

$results = json_decode($result);
$redirectUrl = $results->redirectUrl ?? '';
if (empty($redirectUrl)) {
    // For debugging you may log $result and return an error page
    die('Payment provider did not return a redirect URL');
}

// Redirect to tigopesa Secure Payment URL
echo '<script type="text/javascript">window.location = ' . json_encode($redirectUrl) . ';</script>';
exit;
?>