<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'payment_no',
        'applicant_id',
        'application_id',
        'allocation_id',
        'payment_for',
        'gateway',
        'merchant_invoice_id',
        'gateway_transaction_id',
        'transaction_token',
        'gateway_url',
        'amount',
        'currency',
        'status',
        'paid_at',
        'verified_at',
        'verified_by',
        'gateway_response',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            PlotApplication::class,
            'application_id'
        );
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(
            PlotAllocation::class,
            'allocation_id'
        );
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function gatewayEvents(): HasMany
    {
        return $this->hasMany(PaymentGatewayEvent::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function depositTransactions(): HasMany
    {
        return $this->hasMany(DepositTransaction::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
