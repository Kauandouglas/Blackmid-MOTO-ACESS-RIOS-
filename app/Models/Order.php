<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_document',
        'shipping_address',
        'status',
        'subtotal',
        'shipping_fee',
        'shipping_service',
        'payment_provider',
        'payment_reference',
        'total',
        'paid',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
