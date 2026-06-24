<?php

use WHMCS\Database\Capsule;
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/storage.php';

global $CONFIG;

// Detect module name and fetch gateway params
$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);
$templateName = $CONFIG['Template'] ?? '';

if (empty($gatewayParams['type'])) {
    logTransaction($gatewayModuleName, $_GET, 'module not active');
    die('Payment module is not active');
}

// Safely read incoming params
$trans_status = isset($_GET['trans_status']) ? $_GET['trans_status'] : '';
$transaction_ref_id = isset($_GET['transaction_ref_id']) ? $_GET['transaction_ref_id'] : '';
$verification_code = isset($_GET['verification_code']) ? $_GET['verification_code'] : '';
$external_ref_id = isset($_GET['external_ref_id']) ? $_GET['external_ref_id'] : '';

// Basic validation of transaction_ref_id
if (empty($transaction_ref_id) || strpos($transaction_ref_id, 'DU') === false) {
    logTransaction($gatewayModuleName, $_GET, 'invalid transaction_ref_id');
    // Render a friendly confirmation page still
    $ca = new WHMCS_ClientArea();
    $ca->setPageTitle("Payment Confirmation");
    $ca->addToBreadCrumb('index.php', $CONFIG['CompanyName'] ?? 'Home');
    $ca->initPage();
    $ca->assign('template', $templateName);
    $ca->assign('status', 'error');
    $ca->assign('amount', 0);
    $ca->assign('invoiceUrl', '');
    $ca->setTemplate('tigopesa_callback');
    $ca->output();
    exit;
}

// Determine invoice id from transaction_ref_id
$invoiceId = (int) substr($transaction_ref_id, 0, strpos($transaction_ref_id, "DU"));

// Fetch mapping from DB (server-side saved mapping)
$mapping = tigopesa_get_mapping($transaction_ref_id);

// If no mapping found, fallback to session (backwards compatibility) and then fail
$storedAccess = '';
if (is_array($mapping) && !empty($mapping['access_token'])) {
    $storedAccess = $mapping['access_token'];
} else {
    // Backwards-compatibility: try session token if present
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $storedAccessEncoded = $_SESSION['accessToken'] ?? '';
    if ($storedAccessEncoded) {
        $storedAccess = base64_decode($storedAccessEncoded, true) ?: '';
    }
}

// Prepare amount and validate invoice
$paymentAmount = Capsule::table('tblinvoices')->where('id', $invoiceId)->value('total') ?: 0.00;
$invoiceViewUrl = ($CONFIG['SystemSSLURL'] ?? '') ? ($CONFIG['SystemSSLURL'] . '/viewinvoice.php?id=' . $invoiceId) : ($CONFIG['SystemURL'] . '/viewinvoice.php?id=' . $invoiceId);

// WHMCS invoice validation helpers
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
checkCbTransID($transaction_ref_id);

// Verify token and status
$verified = false;
if ($trans_status === 'success' && is_string($storedAccess) && is_string($verification_code) && $storedAccess !== '') {
    $verified = hash_equals($storedAccess, $verification_code);
}

$transactionIdToRecord = $external_ref_id ?: $transaction_ref_id;

if ($verified) {
    // Record payment
    addInvoicePayment(
        $invoiceId,
        $transactionIdToRecord,
        $paymentAmount,
        0,
        $gatewayModuleName
    );
    logTransaction($gatewayParams['name'], $_GET, "completed");
    // Remove mapping if present
    if ($mapping) {
        tigopesa_delete_mapping($transaction_ref_id);
    }
    // Also remove session token if present
    if (isset($_SESSION['accessToken'])) {
        unset($_SESSION['accessToken']);
    }
} else {
    logTransaction($gatewayParams['name'], $_GET, "failed - verification");
    // Optionally delete mapping to avoid reuse / clean up
    if ($mapping) {
        tigopesa_delete_mapping($transaction_ref_id);
    }
    if (isset($_SESSION['accessToken'])) {
        unset($_SESSION['accessToken']);
    }
}

// Render client area confirmation page
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
exit;