<?php

namespace Tests\Unit;

use App\Services\CheckoutPricing;
use Tests\TestCase;

class CheckoutPricingTest extends TestCase
{
    public function test_it_applies_five_percent_discount_to_pix(): void
    {
        config(['store.pix_discount_percent' => 5]);

        $pricing = new CheckoutPricing();

        $this->assertSame(5.0, $pricing->paymentDiscount('pix', 100.0));
        $this->assertSame(0.0, $pricing->paymentDiscount('card', 100.0));
    }

    public function test_it_applies_pix_discount_after_coupon_discount(): void
    {
        config(['store.pix_discount_percent' => 5]);

        $pricing = new CheckoutPricing();

        $this->assertSame(4.5, $pricing->paymentDiscount('pix', 100.0, 10.0));
    }
}
