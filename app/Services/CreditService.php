<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Cộng/trừ credit cho user VÀ ghi 1 dòng lịch sử (ledger).
     * $amount > 0 = cộng, < 0 = trừ. Khoá hàng user trong transaction để balance_after chính xác.
     *
     * @param  User|int  $user
     * @param  array{description?:string, reference?:Model, admin_id?:int}  $opts
     */
    public static function adjust($user, int $amount, string $type, array $opts = []): CreditTransaction
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return DB::transaction(function () use ($userId, $amount, $type, $opts) {
            /** @var User $u */
            $u = User::lockForUpdate()->findOrFail($userId);
            $u->points = (int) $u->points + $amount;
            $u->save();

            $ref = $opts['reference'] ?? null;

            return CreditTransaction::create([
                'user_id'        => $u->id,
                'amount'         => $amount,
                'balance_after'  => (int) $u->points,
                'type'           => $type,
                'description'    => $opts['description'] ?? null,
                'reference_type' => $ref instanceof Model ? $ref->getMorphClass() : null,
                'reference_id'   => $ref instanceof Model ? $ref->getKey() : null,
                'admin_id'       => $opts['admin_id'] ?? null,
            ]);
        });
    }
}
