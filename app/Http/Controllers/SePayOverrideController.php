<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SePay\SePay\Http\Controllers\SePayController as BaseController;

class SePayOverrideController extends BaseController
{
    public function webhook(Request $request)
    {
        $response = parent::webhook($request);

        $code = $request->string('code')->value();
        $amount = $request->integer('transferAmount');

        if ($request->string('transferType')->value() === 'in' && !empty($code)) {
            try {
                $updated = DB::table('deposits')
                    ->where('transaction_id', $code)
                    ->where('amount', '=', $amount)
                    ->where('status', '!=', 'completed')
                    ->update(['status' => 'completed']);

                if ($updated) {
                    \Log::info('Deposit updated in override controller', [
                        'transaction_id' => $code,
                        'amount' => $amount
                    ]);
                } else {
                    \Log::warning('No deposit updated in override controller', [
                        'transaction_id' => $code
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error updating deposit in override controller', [
                    'error' => $e->getMessage(),
                    'transaction_id' => $code
                ]);
            }
        }

        return $response;
    }
}