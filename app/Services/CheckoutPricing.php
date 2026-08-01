<?php

namespace App\Services;

class CheckoutPricing
{
    public function pixDiscount(float $subtotal, float $couponDiscount = 0): float
    {
        $percentage = max(0, (float) config('store.pix_discount_percent', 5));

        return round(max(0, $subtotal - $couponDiscount) * ($percentage / 100), 2);
    }

    public function paymentDiscount(string $paymentType, float $subtotal, float $couponDiscount = 0): float
    {
        if (strtolower(trim($paymentType)) !== 'pix') {
            return 0.0;
        }

        return $this->pixDiscount($subtotal, $couponDiscount);
    }
}
