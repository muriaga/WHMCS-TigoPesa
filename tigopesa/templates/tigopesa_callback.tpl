<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>{$pagetitle} - {$companyname}ss</title>
    <link href="../templates/{$template}/css/bootstrap.css" rel="stylesheet">
    <link href="templates/{$template}/css/whmcs.css" rel="stylesheet">
    
    <style>
        .payment-confimation {
            text-align: center;
         }
        .payment-confimation h1 {
          color: #88B04B;
          font-weight: 600;
          font-size: 30px;
          margin-top: 40px;
          margin-bottom: 10px;
        }
        .payment-confimation p {
          color: #404F5E;
          font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
          font-size:20px;
          margin: 0;
        }
        .payment-confimation .check i {
            color: #9ABC66;
            font-size: 60px;
            line-height: 100px;
            margin-left:-15px;
        }
        .payment-confimation .uncheck i {
            color: red;
            font-size: 60px;
            line-height: 100px;
            margin-left:-15px;
        }
        .payment-confimation .button{
            margin-top: 40px;
        }
        .payment-confimation .card {
            background: white;
            padding: 60px;
            border-radius: 4px;
            box-shadow: 0 2px 3px #C8D0D8;
            display: inline-block;
            margin: 0 auto;
        }
    </style>
  </head>
    <div class="">
       <div class="content margin-bottom-60 margin-top-60 vh100">
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6 payment-confimation">
            <div class="card border-0 shadow-lg">
                {if $status == "success" }
                    <div class="check" style="border-radius:100px; height:100px; width:100px; background: #F8FAF5; margin:0 auto;">
                        <i class="checkmark">✓</i>
                    </div>
                    <h1>Payment Successfull!</h1> 
                    <p>Your Invoice of Tshs. {$amount} was successfully Paid! Check your Email for Details.
                    </p>
                {elseif $status == "fail"}
                    <div class="uncheck" style="border-radius:100px; height:100px; width:100px; background: #F8FAF5; margin:0 auto;">
                        <i class="checkmark">x</i>
                    </div>
                    <h1 style="color:red">Payment Failed!</h1> 
                    <p>Your Invoice of Tshs. {$amount} was not Paid! Check your account balance or contact us.
                    </p>
                {/if}
                <div class="button d-grid">
                    <a href="{$invoiceUrl}" class="btn btn-success btn-block btn-lg">
                        Back to Invoice
                    </a>
                </div>
            </div>
            
        </div>
        <div class="col-md-3"></div>
    </div>
</div>
    </div>
  </body>
</html>