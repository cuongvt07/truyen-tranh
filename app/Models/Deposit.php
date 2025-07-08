<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = ['status', 'transaction_id', 'amount'];

    public function markCompleted()
    {
        if ($this->status !== 'completed') {
            $this->status = 'completed';
            $this->save();

            \Log::info('Deposit marked as completed', ['id' => $this->id]);
        }
    }
}
