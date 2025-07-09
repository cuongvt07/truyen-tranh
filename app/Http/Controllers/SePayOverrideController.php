<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SePay\SePay\Http\Controllers\SePayController as BaseController;

class SePayOverrideController extends BaseController
{
    public function webhook(Request $request)
    {
        $code = $request->string('code')->value();
        $amount = $request->integer('transferAmount');

        if ($request->string('transferType')->value() === 'in' && !empty($code)) {
            try {
                DB::table('deposits')
                    ->where('transaction_id', $code)
                    ->where('amount', '=', $amount)
                    ->where('status', '!=', 'completed')
                    ->update(['status' => 'completed']);

                $userId = DB::table('deposits')
                    ->where('transaction_id', $code)
                    ->where('amount', '=', $amount)
                    ->value('user_id');

                if ($userId) {
                    User::where('id', $userId)->increment('points', $amount);
                }

                \Log::info('Deposit updated in override controller', [
                    'transaction_id' => $code,
                    'amount' => $amount
                ]);
            } catch (\Exception $e) {
                \Log::error('Error updating deposit in override controller', [
                    'error' => $e->getMessage(),
                    'transaction_id' => $code
                ]);
            }
        }
        
        return parent::webhook($request);
    }

}