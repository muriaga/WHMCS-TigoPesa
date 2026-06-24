<?php

// Require libraries needed for gateway module functions.
use WHMCS\Database\Capsule;
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

global $CONFIG;

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
$templateName = $CONFIG['Template'] ?? '';

if (empty($gatewayParams['type'])) {
    die("TigoPesa Module Not Activated");
}

// Start session to access stored tokens
session_start();

// Safely fetch expected GET params
$trans_status = isset($_GET['trans_status']) ? $_GET['trans_status'] : '';
$transaction_ref_id = isset($_GET['transaction_ref_id']) ? $_GET['transaction_ref_id'] : '';
$verification_code = isset($_GET['verification_code']) ? $_GET['verification_code'] : '';
$external_ref_id = isset($_GET['external_ref_id']) ? $_GET['external_ref_id'] : '';

if (empty($transaction_ref_id) || strpos($transaction_ref_id, 'DU') === false) {
    // Invalid transaction reference
    $trans_status = $trans_status ?: 'error';
    $invoiceId = 0;
} else {
    $invoiceId = substr($transaction_ref_id, 0, strpos($transaction_ref_id, "DU"));
}

$paymentFee = 0;
$transactionId = $transaction_ref_id;

// Fetch invoice total safely
$paymentAmount = Capsule::table('tblinvoices')->where('id', $invoiceId)->value('total') ?: 0.00;

$systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'] . '/' : $CONFIG['SystemURL'] . '/';

//  Validate Callback Invoice ID.
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

//  Check Callback Transaction ID.
checkCbTransID($transactionId);

// Validate, Save Payment, Log Payment
$storedAccessEncoded = $_SESSION['accessToken'] ?? '';
$storedAccess = $storedAccessEncoded ? base64_decode($storedAccessEncoded, true) : '';

// For safety use hash_equals when comparing tokens (both should be strings)
$verified = false;
if ($trans_status === 'success' && is_string($storedAccess) && is_string($verification_code) && $storedAccess !== '' ) {
    // original behavior compared token equality; preserve but use hash_equals
    $verified = hash_equals($storedAccess, $verification_code);
}

$invoiceViewUrl = $systemurl . 'viewinvoice.php?id=' . intval($invoiceId);
$transactionId = $external_ref_id ?: $transaction_ref_id;

if ($verified) {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
    logTransaction($gatewayParams['name'], $_GET, "completed");
    // unset the stored token
    unset($_SESSION['accessToken']);
} else {
    // log failure and unset token
    logTransaction($gatewayParams['name'], $_GET, "failed");
    unset($_SESSION['accessToken']);
}

$ca = new WHMCS_ClientArea();
$ca->setPageTitle("Payment Confirmation");
$ca->addToBreadCrumb('index.php', $CONFIG['CompanyName'] ?? 'Home');
$ca->initPage();
$ca->assign('template', $templateName);
$ca->assign('status', $trans_status);
$ca->assign('amount', $paymentAmount);
$ca->assign('invoiceUrl', $invoiceViewUrl);
$ca->setTemplate('tigopesa_callback');
$ca->output();

?>