<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>{$pagetitle} - {$companyname}ss</title>
    <link href="../templates/{$template}/css/bootstrap.css" rel="stylesheet">
    <link href="templates/{$template}/css/whmcs.css" rel="stylesheet">
    
    <style>
        html,body{
            font-size: 14px !important;
        }
    </style>
  </head>
    <div class="">
       <div class="content margin-bottom-60 margin-top-60 vh100">
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <form action="{$createPaymentUrl}" method="POST" autocomplete="off">
                <input type="hidden" name="data" value="{$data}" />
                <input type="hidden" name="accessToken" value="{$accessToken}" />
                <div class="form-group mb-3">
                  <label class="mb-2" for=""><h5><strong>Phone Number</strong></h5></label>
                  <input id="phone" class="form-control" type="tel" name="phone" pattern="^+255[0-9]{9}$" title="+255xxxxxxxxx" placeholder="Enter Tigopesa phonenumber" required/>
                </div>
                <div class="mb-3 mt-3 d-grid">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Pay Tshs. {$amount}/=</button>
                    <p class="mb-2 mt-2 text-secondary text-center">ORDER ID:{$invoiceid}</p>
                </div>

                <div class="my-2">
                    <p>Please keep your phone in hand. Once you click "Pay" a request to comfirm your PIN sent to your phone.</p>
                </div>
            </form>
            
            <div class="text-center" style="margin-top: 30px">
                <img src="logo.png" height="40" width="auto" alt="tigopesa">
            </div>

        </div>
        <div class="col-md-4"></div>
    </div>
</div>
    </div>
  </body>
</html>