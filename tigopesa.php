<?php
   

    if (!defined("WHMCS")) {
        die("This file cannot be accessed directly");
    }

    //  Define module related meta data.
    function tigopesa_MetaData(){
        return array(
            'DisplayName' => 'TigoPesa Payment',
            'APIVersion' => '1.1', // Use API Version 1.1
            'DisableLocalCreditCardInput' => true,
            'TokenisedStorage' => false,
        );
    }

    // Define gateway configuration options.
    function tigopesa_config() {
        
        global $CONFIG;
        copyTigopesaTemplates();
    
        $configArray = array(
            // the friendly display name for a payment gateway should be
            'FriendlyName' => array(
                'Type' => 'System',
                'Value' => 'TigoPesa Payment',
            ),
            // Text Field for TIGOPESA API KEY
            'apiKey' => array(
                'FriendlyName' => 'API KEY',
                'Type' => 'text',
                'Size' => '50',
                'Description' => 'Enter your account API KEY here',
            ),
            // Text Field for TIGOPESA SECRET KEY
            'secretKey' => array(
                'FriendlyName' => 'Secret Key',
                'Type' => 'password',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter secret key here',
            ),
            // A customer MSISDN
            'customerMSISDN' => array(
                'FriendlyName' => 'customer MSISDN',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter customer MSISDN here',
            ),
            // Customer PIN
            'customerPIN' => array(
                'FriendlyName' => 'Customer PIN',
                'Type' => 'password',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter customer PIN here',
            ),
             // Account ID
             'accountID' => array(
                'FriendlyName' => 'Account ID',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter account ID here',
            ),
            // Generate accessToken URL
            'access_token_url' => array(
                'FriendlyName' => 'Generate AccessToken Url',
                'Type' => 'text',
                'Size' => '50',
                'Default' => '',
                'Description' => 'Enter url here',
            ),
            // Generate Validate MFS URL
            'validate_MFS_url' => array(
                'FriendlyName' => 'Validate MFS url',
                'Type' => 'text',
                'Size' => '50',
                'Default' => '',
                'Description' => 'Enter url here',
            ),
            // Generate Payment url
            'paymentURL' => array(
                'FriendlyName' => 'Make Payment URL',
                'Type' => 'text',
                'Size' => '50',
                'Default' => '',
                'Description' => 'Enter url here',
            ),
            
        );
    
        return $configArray;
    }


    // Payment link.
    function tigopesa_link($data) {
        global $CONFIG;
        
        $systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'] . '/' : $CONFIG['SystemURL'] . '/';
    
        $data = serialize($data);
        $data = base64_encode($data);
        
        $gatewayConfigs = getGatewayVariables("tigopesa");
    
        $baseUrl = ($gatewayConfigs['basedomainurl']) ? $gatewayConfigs['basedomainurl'] : $systemurl;
    
        $processPaymentLink = $baseUrl . 'modules/gateways/tigopesa/processPayment.php';
        
        $code = '<form method="POST" action="' . $processPaymentLink . '">
        <input type="hidden" name="order" value="' . $data . '" />
        <input class="btn btn-primary btn-lg" type="submit" value="Pay Now" />
        </form>';
        return $code;
    }

    // Copy templates files to /templates/template_name/ directory
    function copyTigopesaTemplates() {
        global $CONFIG;
    
        try {
            if (!isset($CONFIG['Template'])) {
                throw new Exception("Template not set. Cannot discover Template Name", 1);
            }
    
            $templateName = $CONFIG['Template'];
            $templatePath = __DIR__ . '/../../templates/' . $templateName . '/';
            if (!file_exists($templatePath) || !$templateName) {
                throw new Exception("Template \"$templateName\" doesn't exist.", 1);
            }
    
            $tigopesaFinalTemplatePaths = array(
                __DIR__ . '/../../templates/' . $templateName . '/tigopesa_payment.tpl',
                __DIR__ . '/../../templates/' . $templateName . '/tigopesa_callback.tpl'
            );
    
            $sourceDirectory = __DIR__ . '/tigopesa/templates/';
            $destinationDirectory = __DIR__ . '/../../templates/' . $templateName . '/';
            
            $templatesToCopy = array(
                'tigopesa_payment.tpl',
                'tigopesa_callback.tpl'
            );
    
            foreach ($templatesToCopy AS $templateToCopy) {
                $sourceTemplatePath = $sourceDirectory . $templateToCopy;
                $destinationTemplatePath = $destinationDirectory . $templateToCopy;
                
                if (!file_exists($destinationTemplatePath)) {
                    if (!file_exists($sourceTemplatePath)) {
                        throw new Exception("TigoPesa Source Template ('$templateToCopy') Not Found. Please ensure to copy all the files correctly", 2);
                    }
    
                    $copySuccess = copy($sourceTemplatePath, $destinationTemplatePath);
    
                    if (!$copySuccess) {
                        throw new Exception("Copy $sourceTemplatePath to $destinationTemplatePath Failed", 3);
                    }
                    // show here message to log for success copy
                }
            }
        } catch (Exception $ex) {
             // show here message to log for error copy
        }
    }

?>
