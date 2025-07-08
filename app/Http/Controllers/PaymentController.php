<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function createDeposit(Request $request)
    {
        $amount = $request->input('amount');
        $userId = auth()->id();

        $chargeId = 'WEB' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

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
            'bank_name' => 'MBBank',  // Tên ngân hàng
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
            'bank' => 'MBBank',  // Lấy tên ngân hàng
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
        $chargeId = $request->input('charge_id');

        $deposit = DB::table('deposits')->where('transaction_id', $chargeId)->first();

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
