<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCart extends Model
{
    protected $fillable = [
        'session_id',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'cart_items',
        'subtotal',
        'items_count',
        'converted_at',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'cart_items'    => 'array',
            'subtotal'      => 'decimal:2',
            'converted_at'  => 'datetime',
        ];
    }

    /* ---------- Relations ---------- */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /* ---------- Scopes ---------- */

    /** Carrinhos abandonados: 6 h+ sem conversão */
    public function scopeAbandoned(Builder $q): Builder
    {
        return $q->whereNull('converted_at')
                  ->where('created_at', '<=', now()->subHours(6))
                  ->whereNotNull('customer_email');
    }

    /** Carrinhos que foram convertidos em pedido */
    public function scopeConverted(Builder $q): Builder
    {
        return $q->whereNotNull('converted_at');
    }

    /** Carrinhos pendentes: ainda dentro da janela de 6 h */
    public function scopePending(Builder $q): Builder
    {
        return $q->whereNull('converted_at')
                  ->where('created_at', '>', now()->subHours(6))
                  ->whereNotNull('customer_email');
    }

    /* ---------- Helpers ---------- */

    public function fullName(): string
    {
        return trim(($this->customer_first_name ?? '') . ' ' . ($this->customer_last_name ?? ''));
    }

    public function isAbandoned(): bool
    {
        return is_null($this->converted_at) && $this->created_at->lte(now()->subHours(6));
    }
}
