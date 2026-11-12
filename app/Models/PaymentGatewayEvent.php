<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayEvent extends Model
{
    protected $fillable = [
        'payment_id',
        'gateway_event_id',
        'event_type',
        'payload',
        'signature_valid',
        'is_processed',
        'processed_at',
        'processing_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'signature_valid' => 'boolean',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
