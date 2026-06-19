<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function createDeposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', Rule::in([20000, 50000, 100000])],
        ]);
        $amount = (int) $validated['amount'];
        $userId = (int) $request->user()->id;

        do {
            $chargeId = 'WEB' . Str::upper(Str::random(20));
        } while (DB::table('deposits')->where('transaction_id', $chargeId)->exists());

        $depositId = DB::table('deposits')->insertGetId([
            'user_id' => $userId,
            'amount' => $amount,
            'payment_method' => 'sepay',
            'transaction_id' => $chargeId,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deposit = DB::table('deposits')->where('id', $depositId)->first();

        // Tạo URL QR
        $qrCodeUrl = $this->generateQRCodeUrl($amount, $chargeId);

        // Trả về thông tin mã QR và thông tin giao dịch
        return response()->json([
            'qr_code_url' => $qrCodeUrl,  // Mã QR
            'charge_id' => $chargeId,  // Mã giao dịch
            'amount' => $amount,  // Số tiền
            'status' => $deposit->status,  // Trạng thái giao dịch
            'bank_name' => setting('bank1_name'),  // Tên ngân hàng
            'account_number' => setting('bank1_account_number'),  // Số tài khoản
            'account_holder' => setting('bank1_account_name'),  // Tên tài khoản
        ]);
    }

    /**
     * Tạo URL mã QR dựa trên số tiền và mã giao dịch.
     */
    public function generateQRCodeUrl(float $amount, string $chargeId): string
    {
        return 'https://qr.sepay.vn/img?' . http_build_query([
            'acc' => setting('bank1_account_number'),  // Lấy số tài khoản
            'bank' => setting('bank1_name'),  // Lấy tên ngân hàng
            'amount' => $amount,  // Số tiền giao dịch
            'des' => $chargeId,  // Mã giao dịch
            'template' => 'compact',  // Tùy chỉnh kiểu QR
        ]);
    }

    public function showPaypoints()
    {
        return view('client.paypoints.pay-points');
    }

    public function checkTransactionStatus(Request $request)
    {
        $validated = $request->validate([
            'charge_id' => ['required', 'string', 'max:64'],
        ]);

        $deposit = DB::table('deposits')
            ->where('transaction_id', $validated['charge_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$deposit) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $deposit->status,
            'amount' => $deposit->amount,
            'created_at' => $deposit->created_at,
        ]);
    }
}
