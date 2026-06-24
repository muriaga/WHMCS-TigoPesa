## WHMCS TigoPesa Payment Gateway
WHMCS TigoPesa Payment Gateway API Integration (Tigo Tanzania)

Payment Gateway to allow you to integrate payment solutions with the WHMCS platform

## Settings and Installation Procedures

Gateway Module Integration Procedures:

* Download the zip file, Upload and unzip it to your WHMCS's modules/gateways folder.

* Ensure the `tigopesa.php` file is in `modules/gateways/`, the `tigopesa/` folder is in `modules/gateways/` and the callback `modules/gateways/callback/tigopesa.php` is present.

* Enable the TigoPesa module in the WHMCS admin area by going to Settings → Apps & Integrations → Payments → Payments Apps → TigoPesa Payments.

* Fill in your API Key, Secret Key, Merchant MSISDN, Merchant PIN, Account ID, AccessToken URL, Payment URL and Validate MFS URL in the gateway configuration.

* To get your API credentials and gateway URLs, contact TigoPesa Tech Support (see their docs).

* Save your settings and test in the sandbox environment. When you're ready to go live, replace the sandbox credentials with the live ones provided by TigoPesa.

Note: Merchant MSISDN and Account ID are often identical; confirm with TigoPesa.

## Changes in this fork
This fork includes several security and reliability improvements over the original module:

- Uses base64-encoded JSON instead of PHP serialize/unserialize for order payloads (avoids unsafe unserialize on untrusted input).
- Stores transactionRefId → accessToken → invoiceId mapping server-side in `tbl_tigopesa_transactions` (DB table) so callbacks are reliable even when sessions are not preserved.
- Safer token verification using `hash_equals()` and improved error handling and logging via `logTransaction()`.
- Defensive cURL and JSON handling across the flow.

## New database table
The module creates `tbl_tigopesa_transactions` automatically (when needed). If your environment disables schema changes, run this SQL manually in your WHMCS database:

```sql
CREATE TABLE IF NOT EXISTS tbl_tigopesa_transactions (
  transaction_ref_id VARCHAR(100) PRIMARY KEY,
  access_token TEXT NOT NULL,
  invoice_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## How the flow works now
1. Customer clicks the TigoPesa Pay Now button on an invoice. The module uses a base64(JSON) order payload.
2. `processPayment.php` requests an access token from the configured `access_token_url` and stores it in PHP session (base64) for short-term compatibility.
3. `createPayment.php` decodes the order payload, generates a `transactionRefId`, saves the mapping (transactionRefId → accessToken → invoiceId) to `tbl_tigopesa_transactions`, calls the payment provider, and redirects the client to the provider's `redirectUrl`.
4. Provider performs the payment flow and calls back to `modules/gateways/callback/tigopesa.php`.
5. The callback handler looks up the mapping by `transaction_ref_id` (server-side), verifies `verification_code` using `hash_equals()` against the stored access token, records the payment with `addInvoicePayment()`, logs via `logTransaction()`, and deletes the mapping.

## How to run (short)
```bash
# From a clone:
cp -r WHMCS-TigoPesa/* /path/to/whmcs/modules/gateways/
# Ensure file permissions allow the webserver to read the files and write sessions.
# In WHMCS Admin: enable the TigoPesa gateway and configure credentials & URLs.
```

## Testing checklist
1. Backup the module files and DB.
2. Install the module and configure gateway settings.
3. Create a test invoice and click Pay Now.
4. Confirm `processPayment.php` fetches an access token and renders the payment page.
5. Confirm `createPayment.php` saves a mapping (check `tbl_tigopesa_transactions`) and redirects to provider.
6. Simulate provider callback to `modules/gateways/callback/tigopesa.php` with `trans_status=success`, `transaction_ref_id` (the saved reference), and `verification_code` (the saved access token).
7. Confirm invoice is marked paid, mapping is deleted, and logs show success.

## Troubleshooting & Notes
- Callbacks may not reuse the PHP session — the DB mapping ensures callbacks succeed even if sessions are different.
- If your environment disallows automatic schema changes, run the SQL above to add the mapping table.
- Replace `die()` debug messages with `logTransaction()` or user-friendly pages before deploying to production.

## Languages used
- PHP
- Smarty

## Useful API Reference
* https://developers.whmcs.com
* https://developers.whmcs.com/hooks
* https://developers.whmcs.com/api
