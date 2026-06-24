<?php

use WHMCS\Database\Capsule;

/**
 * tigopesa storage helpers
 * Stores mapping between transactionRefId and access token so callbacks can be verified
 */

function tigopesa_ensure_table() {
    try {
        if (!Capsule::schema()->hasTable('tbl_tigopesa_transactions')) {
            Capsule::schema()->create('tbl_tigopesa_transactions', function($table) {
                $table->string('transaction_ref_id', 100)->primary();
                $table->text('access_token');
                $table->integer('invoice_id')->unsigned();
                $table->timestamp('created_at')->nullable();
            });
        }
    } catch (Exception $e) {
        // If schema builder not available or creation fails, ignore here; callers will handle errors
    }
}

function tigopesa_save_mapping($transactionRefId, $accessToken, $invoiceId) {
    try {
        tigopesa_ensure_table();
        Capsule::table('tbl_tigopesa_transactions')->updateOrInsert(
            ['transaction_ref_id' => $transactionRefId],
            ['access_token' => $accessToken, 'invoice_id' => $invoiceId, 'created_at' => date('Y-m-d H:i:s')]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function tigopesa_get_mapping($transactionRefId) {
    try {
        tigopesa_ensure_table();
        $row = Capsule::table('tbl_tigopesa_transactions')->where('transaction_ref_id', $transactionRefId)->first();
        if ($row) {
            return (array)$row;
        }
        return null;
    } catch (Exception $e) {
        return null;
    }
}

function tigopesa_delete_mapping($transactionRefId) {
    try {
        tigopesa_ensure_table();
        return Capsule::table('tbl_tigopesa_transactions')->where('transaction_ref_id', $transactionRefId)->delete();
    } catch (Exception $e) {
        return false;
    }
}
