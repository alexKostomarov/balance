<?php

namespace App\Models;

use App\BalanceApp\Domain\Transaction\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $keyType = 'string';  // UUID — это строка
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'type', // 'deposit', 'withdraw'
        'amount',
        'created_at',
        'comment',
        'currency',
        'related_transaction_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'amount' => 'integer',
        'user_id' => 'string',
        'related_transaction_id' => 'string',
        'created_at' => 'datetime',
        'comment' => 'string',
        'currency' => 'string',
        'type' => TransactionType::class
    ];

    public function balance(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Balance::class, 'user_id', 'user_id');
    }
}
