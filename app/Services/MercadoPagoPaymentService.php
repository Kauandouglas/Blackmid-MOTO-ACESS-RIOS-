<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MercadoPagoPaymentService
{
    public function verifyWebhookSignature(array $headers, string $payload): bool
    {
        $webhookSecret = (string) config('services.mercadopago.webhook_secret');

        if (empty($webhookSecret)) {
            throw new RuntimeException('MERCADO_PAGO_WEBHOOK_SECRET não configurada.');
        }

        $signature = $headers['x-signature'] ?? '';

        if (empty($signature)) {
            throw new RuntimeException('Assinatura do webhook Mercado Pago ausente.');
        }

        // Verificar assinatura usando SHA256
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Assinatura Mercado Pago não confere.');
        }

        return true;
    }

    /**
     * Cria uma preferência de pagamento no Mercado Pago
     */
    public function createPaymentPreference(Order $order): array
    {
        $accessToken = (string) config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            throw new RuntimeException('MERCADO_PAGO_ACCESS_TOKEN não configurada.');
        }

        $items = [];
        $subtotal = max(0.01, (float) $order->subtotal);
        $discount = max(0, ((float) $order->subtotal + (float) $order->shipping_fee) - (float) $order->total);
        $discountFactor = max(0, ((float) $order->subtotal - $discount) / $subtotal);

        foreach ($order->items as $item) {
            $items[] = [
                'id'       => (string) $item->product_id,
                'title'    => $item->product_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => round(max(0.01, (float) $item->unit_price * $discountFactor), 2),
            ];
        }

        $payload = [
            'items'            => $items,
            'payer'            => [
                'email' => $order->customer_email,
                'name'  => $order->customer_name,
            ],
            'back_urls'        => [
                'success' => route('checkout.payment.mercadopago.success', $order),
                'failure' => route('checkout.payment.cancel', $order),
                'pending' => route('checkout.payment.mercadopago.pending', $order),
            ],
            'auto_return'      => 'approved',
            'external_reference' => (string) $order->id,
            'shipments'        => [
                'cost'           => (float) $order->shipping_fee,
                'mode'           => 'custom',
                'receiver_address' => [
                    'zip_code' => '00000',
                    'street_name' => 'Endereço',
                    'street_number' => 0,
                ],
            ],
            'notification_url' => route('webhooks.mercadopago'),
            'payment_methods'   => [
                'excluded_payment_types' => [
                    ['id' => 'account_money'],
                    ['id' => 'atm'],
                ],
                'installments' => 12,
            ],
            'metadata'         => [
                'order_id' => $order->id,
                'order_number' => $order->number,
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post('https://api.mercadopago.com/checkout/preferences', $payload);

        if (!$response->successful()) {
            Log::error('Mercado Pago preference falhou', [
                'status'  => $response->status(),
                'response' => $response->json(),
            ]);

            $mpError = $response->json('message') ?? 'Falha ao criar preferência Mercado Pago.';
            throw new RuntimeException($mpError);
        }

        $data = $response->json();

        if (empty($data['init_point'])) {
            throw new RuntimeException('Mercado Pago não retornou init_point.');
        }

        return [
            'id'         => $data['id'] ?? null,
            'init_point' => $data['init_point'],
        ];
    }

    /**
     * Obtém informações de um pagamento
     */
    public function getPaymentInfo(string $paymentId): array
    {
        $accessToken = (string) config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            throw new RuntimeException('MERCADO_PAGO_ACCESS_TOKEN não configurada.');
        }

        $response = Http::withToken($accessToken)
            ->get('https://api.mercadopago.com/v1/payments/' . $paymentId);

        if (!$response->successful()) {
            throw new RuntimeException('Falha ao consultar pagamento Mercado Pago.');
        }

        return $response->json();
    }

    /**
     * Parse webhook event do Mercado Pago
     */
    public function parseWebhookEvent(array $payload): array
    {
        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];

        if ($type === 'payment') {
            $paymentId = $data['id'] ?? null;

            if (!$paymentId) {
                throw new RuntimeException('Payment ID não encontrado no webhook.');
            }

            $payment = $this->getPaymentInfo((string) $paymentId);

            return [
                'type'           => 'payment',
                'payment_id'     => $paymentId,
                'status'         => $payment['status'] ?? '',
                'order_id'       => $payment['external_reference'] ?? null,
                'amount'         => $payment['transaction_amount'] ?? 0,
                'payment_type'   => $payment['payment_type_id'] ?? '',
            ];
        }

        return [];
    }
}
