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
    $templateName = $CONFIG['Template'];
    
    // Die if module is not active.
    if (!$gatewayParams['type']) {
        die("TigoPesa Module Not Activated");
    }
    
    // Retrieve data returned in payment gateway callback
    $trans_status = $_GET['trans_status'];
    $transaction_ref_id = $_GET['transaction_ref_id'];
    $verification_code = $_GET['verification_code'];
    $external_ref_id = $_GET['external_ref_id'];
    
    $paymentFee = 0;
    $invoiceId = substr($transaction_ref_id, 0, strpos($transaction_ref_id, "DU"));
    
    $transactionId = $transaction_ref_id;
    
    $paymentAmount = Capsule::table('tblinvoices')->where('id', $invoiceId)->value('total');
    
    $systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'] . '/' : $CONFIG['SystemURL'] . '/';
    
    //  Validate Callback Invoice ID.
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
    
    //  Check Callback Transaction ID.
    checkCbTransID($transactionId);
    
    // Validate, Save Payment, Log Payment
    $accessToken = $_SESSION['accessToken']; // Get accessToken from session
    $accessToken = base64_decode($accessToken);
    
    $ivoiceUrl = $systemurl . 'viewinvoice.php?id=' . $invoiceId;
    $transactionId = $external_ref_id;
    
    if ($verification_code == $accessToken && $trans_status == 'success') {
        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $paymentAmount,
            $paymentFee,
            $gatewayModuleName
        );
        logTransaction($gatewayParams['name'], $_POST, "completed");
        
        // unset a session variable
        unset($_SESSION['accessToken']);
    } else {
        // unset a session variable
        unset($_SESSION['accessToken']);
    }
    
    $ca = new WHMCS_ClientArea();
    $ca->setPageTitle("Payment Confirmation");
    $ca->addToBreadCrumb('index.php', $orderDetails['globalsystemname']);
    $ca->initPage();
    $ca->assign('template', $templateName);
    $ca->assign('status', $trans_status);
    $ca->assign('amount', $paymentAmount);
    $ca->assign('invoiceUrl', $ivoiceUrl);
    $ca->setTemplate('tigopesa_callback'); 
    $ca->output();
  
?>
