<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRequest extends Model
{
    protected $table = 'subscription_requests';

    protected $fillable = [
        'user_id', 'requested_plan', 'status', 'keterangan', 'amount', 'payment_method', 'bukti_path', 'paid_at', 'approved_at', 'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}