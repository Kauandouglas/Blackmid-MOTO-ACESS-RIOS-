<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Services\MercadoPagoPaymentService;
use App\Services\BrazilShipping;
use App\Services\CheckoutPricing;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $cart = $this->normalizeCart();

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $productIds = collect($cart)->pluck('product_id')->unique()->all();
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        [$items, $subtotal, $totalWeightGrams] = $this->buildCartSnapshot($cart, $products);

        $quote         = $this->shippingService()->getQuote($totalWeightGrams, $subtotal);
        $shippingRates = $quote['rates'];
        $shipping      = $quote['shipping'];
        $couponDiscount = 0.0;
        $paymentDiscount = $this->pricingService()->paymentDiscount('pix', $subtotal, $couponDiscount);
        $discount = $couponDiscount + $paymentDiscount;

        return response()
            ->view('checkout.index', [
                'items'         => $items,
                'subtotal'      => $subtotal,
                'shippingRates' => $shippingRates,
                'shipping'      => $shipping,
                'shippingSource'=> $quote['source'],
                'hasShippingApi' => $quote['has_api'],
                'discount'      => $discount,
                'couponDiscount' => $couponDiscount,
                'paymentDiscount' => $paymentDiscount,
                'pixDiscountPercent' => (float) config('store.pix_discount_percent', 5),
                'couponCode'    => '',
                'total'         => max(0, $subtotal - $discount) + $shipping,
                'cartCount'     => (int) collect($cart)->sum('quantity'),
                'enabledGateways' => AdminPaymentController::enabledGateways(),
                'mercadoPagoPublicKey' => (string) config('services.mercadopago.public_key'),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shipping_postcode' => ['required', 'string', 'max:20'],
            'shipping_address_line1'  => ['nullable', 'string', 'max:180'],
            'shipping_address_line2'  => ['nullable', 'string', 'max:180'],
            'shipping_city'           => ['nullable', 'string', 'max:120'],
            'shipping_country'        => ['nullable', 'string', 'size:2'],
            'customer_name'     => ['nullable', 'string', 'max:120'],
            'customer_email'    => ['nullable', 'email', 'max:120'],
            'customer_phone'    => ['nullable', 'string', 'max:30'],
            'coupon_code'       => ['nullable', 'string', 'max:40'],
            'payment_type'      => ['nullable', 'string', 'in:pix,card'],
        ]);

        $cart = $this->normalizeCart();
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Seu carrinho está vazio.',
            ], 422);
        }

        $productIds = collect($cart)->pluck('product_id')->unique()->all();
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');
        [, $subtotal, $totalWeightGrams] = $this->buildCartSnapshot($cart, $products);

        $discount = $this->couponDiscount((string) ($data['coupon_code'] ?? ''), $subtotal);
        $paymentDiscount = $this->paymentDiscount((string) ($data['payment_type'] ?? 'pix'), $subtotal, $discount);
        $totalDiscount = $discount + $paymentDiscount;

        $quote = $this->shippingService()->getQuote(
            $totalWeightGrams,
            $subtotal,
            $this->destinationFromData($data)
        );

        return response()->json([
            'success'         => true,
            'source'          => $quote['source'],
            'shipping'        => $quote['shipping'],
            'shipping_label'  => $quote['shipping'] > 0 ? 'R$ ' . number_format($quote['shipping'], 2, ',', '.') : 'Gratis',
            'discount'        => $totalDiscount,
            'discount_label'  => $totalDiscount > 0 ? '-R$ ' . number_format($totalDiscount, 2, ',', '.') : 'R$ 0,00',
            'coupon_discount' => $discount,
            'payment_discount' => $paymentDiscount,
            'payment_discount_label' => $paymentDiscount > 0 ? '-R$ ' . number_format($paymentDiscount, 2, ',', '.') : 'R$ 0,00',
            'coupon_valid'    => $discount > 0,
            'total'           => max(0, $subtotal - $totalDiscount) + $quote['shipping'],
            'total_label'     => 'R$ ' . number_format(max(0, $subtotal - $totalDiscount) + $quote['shipping'], 2, ',', '.'),
            'rates'           => $quote['rates'],
            'has_api'         => $quote['has_api'],
        ]);
    }

    public function captureCart(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_first_name' => ['nullable', 'string', 'max:80'],
            'customer_last_name'  => ['nullable', 'string', 'max:80'],
            'customer_email'      => ['required', 'email', 'max:120'],
            'customer_phone'      => ['nullable', 'string', 'max:30'],
        ]);

        $cart = $this->normalizeCart();
        if (empty($cart)) {
            return response()->json(['success' => true, 'captured' => false]);
        }

        $productIds = collect($cart)->pluck('product_id')->unique()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $subtotal = 0;
        $itemsCount = 0;

        foreach ($cart as $entry) {
            $product = $products->get((int) ($entry['product_id'] ?? 0));
            if (! $product) {
                continue;
            }

            $quantity = (int) ($entry['quantity'] ?? 0);
            $lineTotal = (float) $product->price * $quantity;
            $subtotal += $lineTotal;
            $itemsCount += $quantity;

            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'size' => $entry['size'] ?? null,
                'color' => $entry['color'] ?? null,
                'unit_price' => (float) $product->price,
                'line_total' => $lineTotal,
                'image' => $product->image,
            ];
        }

        AbandonedCart::query()->updateOrCreate(
            [
                'session_id' => session()->getId(),
                'converted_at' => null,
            ],
            [
                'customer_first_name' => $data['customer_first_name'] ?? null,
                'customer_last_name' => $data['customer_last_name'] ?? null,
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'cart_items' => $items,
                'subtotal' => $subtotal,
                'items_count' => $itemsCount,
            ],
        );

        return response()->json(['success' => true, 'captured' => true]);
    }

    public function process(Request $request): JsonResponse|RedirectResponse
    {
        $wantsJson = $request->expectsJson();

        $data = $request->validate([
            'customer_first_name' => ['required', 'string', 'max:80'],
            'customer_last_name'  => ['required', 'string', 'max:80'],
            'customer_email'   => ['required', 'email', 'max:120'],
            'customer_phone'   => ['required', 'string', 'max:30'],
            'customer_document' => ['required', 'string', 'min:11', 'max:20'],
            'shipping_country' => ['required', 'string', 'size:2'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_state' => ['nullable', 'string', 'max:80'],
            'shipping_neighborhood' => ['nullable', 'string', 'max:120'],
            'shipping_address_line1' => ['required', 'string', 'max:180'],
            'shipping_number' => ['nullable', 'string', 'max:30'],
            'shipping_address_line2' => ['nullable', 'string', 'max:180'],
            'shipping_postcode'=> ['required', 'string', 'max:20'],
            'shipping_method'  => ['required', 'string', 'in:pac,sedex'],
            'payment_method'   => ['required', 'string', 'in:' . implode(',', AdminPaymentController::enabledGateways())],
            'payment_type'     => ['required_if:payment_method,mercadopago', 'nullable', 'string', 'in:pix,card'],
            'mp_token'         => ['required_if:payment_type,card', 'nullable', 'string', 'max:200'],
            'mp_payment_method_id' => ['required_if:payment_type,card', 'nullable', 'string', 'max:60'],
            'mp_issuer_id'     => ['nullable', 'string', 'max:60'],
            'mp_installments'  => ['nullable', 'integer', 'min:1', 'max:12'],
            'coupon_code'      => ['nullable', 'string', 'max:40'],
            'newsletter_opt_in' => ['nullable', 'boolean'],
            'save_info' => ['nullable', 'boolean'],
        ]);

        $data['customer_name'] = trim($data['customer_first_name'] . ' ' . $data['customer_last_name']);
        $data['customer_document'] = preg_replace('/\D+/', '', (string) $data['customer_document']);

        $cart = $this->normalizeCart();

        if (empty($cart)) {
            if ($wantsJson) {
                return response()->json(['error' => 'Seu carrinho está vazio.'], 422);
            }
            return redirect()->route('cart.index')->with('error', 'Seu carrinho esta vazio.');
        }

        $productIds = collect($cart)->pluck('product_id')->unique()->all();
        $products   = Product::query()
            ->whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== count($productIds)) {
            if ($wantsJson) {
                return response()->json(['error' => 'Um ou mais produtos não existem mais.'], 422);
            }
            return redirect()->route('cart.index')->with('error', 'Um ou mais produtos nao existem mais.');
        }

        [, , $totalWeightGrams] = $this->buildCartSnapshot($cart, $products);
        $shippingService = $this->shippingService();
        $selectedService = $data['shipping_method'];
        $destination     = $this->destinationFromData($data);

        try {
            $order = DB::transaction(function () use ($data, $cart, $products, $shippingService, $selectedService, $totalWeightGrams, $destination) {
                $subtotal = 0;

                foreach ($cart as $entry) {
                    $product  = $products->get((int) $entry['product_id']);
                    $quantity = (int) ($entry['quantity'] ?? 0);
                    $size = trim((string) ($entry['size'] ?? ''));
                    $color = trim((string) ($entry['color'] ?? ''));

                    $hasInventory = (bool) ($product->track_stock ?? true);

                    if (! $product || ! $product->active) {
                        throw ValidationException::withMessages([
                            'stock' => 'Produto sem estoque suficiente para finalizar o pedido.',
                        ]);
                    }

                    if ($hasInventory) {
                        $variant = ProductVariant::query()
                            ->where('product_id', $product->id)
                            ->where('size', $size)
                            ->where('color', $color)
                            ->lockForUpdate()
                            ->first();

                        $availableStock = $variant ? (int) $variant->stock : (int) $product->stock;

                        if ($availableStock < $quantity) {
                            throw ValidationException::withMessages([
                                'stock' => 'Produto sem estoque suficiente para finalizar o pedido.',
                            ]);
                        }
                    }

                    if ($hasInventory && $product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'stock' => 'Produto sem estoque suficiente para finalizar o pedido.',
                        ]);
                    }

                    $subtotal += $product->price * $quantity;
                }

                $shippingFee = $shippingService->getPrice($selectedService, $totalWeightGrams, $subtotal, $destination);
                $couponDiscount = $this->couponDiscount((string) ($data['coupon_code'] ?? ''), $subtotal);
                $paymentDiscount = $this->paymentDiscount((string) ($data['payment_type'] ?? 'pix'), $subtotal, $couponDiscount);
                $discount = $couponDiscount + $paymentDiscount;

                $order = Order::create([
                    'number'           => 'OB-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                    'customer_name'    => $data['customer_name'],
                    'customer_email'   => $data['customer_email'],
                    'customer_phone'   => $data['customer_phone'],
                    'customer_document' => $data['customer_document'],
                    'shipping_address' => $this->formatShippingAddress($data),
                    'status'           => 'received',
                    'subtotal'         => $subtotal,
                    'shipping_fee'     => $shippingFee,
                    'shipping_service' => $selectedService,
                    'payment_provider' => $data['payment_method'],
                    'total'            => max(0, $subtotal - $discount) + $shippingFee,
                    'paid'             => false,
                ]);

                foreach ($cart as $entry) {
                    $product  = $products->get((int) $entry['product_id']);
                    $quantity = (int) ($entry['quantity'] ?? 0);
                    $size = trim((string) ($entry['size'] ?? ''));
                    $color = trim((string) ($entry['color'] ?? ''));

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'size'         => $size ?: null,
                        'color'        => $color ?: null,
                        'quantity'     => $quantity,
                        'unit_price'   => $product->price,
                        'subtotal'     => $product->price * $quantity,
                    ]);
                }

                return $order;
            });
        } catch (ValidationException $exception) {
            if ($wantsJson) {
                return response()->json(['errors' => $exception->errors()], 422);
            }
            return redirect()
                ->route('cart.index')
                ->withErrors($exception->errors())
                ->withInput();
        } catch (Throwable $exception) {
            Log::error('Falha ao processar checkout', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            if ($wantsJson) {
                return response()->json([
                    'error' => config('app.debug')
                        ? $exception->getMessage()
                        : 'Nao foi possivel processar o pedido agora. Tente novamente.',
                ], 500);
            }

            return redirect()
                ->route('checkout.index')
                ->with('error', 'Nao foi possivel processar o pedido agora. Tente novamente.');
        }

        return $this->startPayment($order, $data, $wantsJson);
    }
    public function paymentCancel(Order $order): RedirectResponse
    {
        // Se o pedido já foi pago (webhook pode ter confirmado), não cancelar
        if ($order->paid) {
            return redirect()->route('checkout.success', $order);
        }

        // Se já foi cancelado antes, apenas redirecionar
        if ($order->status === 'payment_cancelled') {
            return redirect()->route('checkout.index')->with('error', 'Pagamento cancelado. Você pode tentar novamente.');
        }

        $order->update([
            'status' => 'payment_cancelled',
        ]);

        // Reverter conversão do carrinho abandonado vinculado a este pedido
        $this->unconvertAbandonedCart($order);

        return redirect()->route('checkout.index')->with('error', 'Pagamento cancelado. Você pode tentar novamente.');
    }

    public function mercadopagoSuccess(Request $request, Order $order): RedirectResponse
    {
        $mpPaymentId = (string) $request->query('payment_id', '');
        if ($mpPaymentId === '') {
            return redirect()->route('checkout.index')->with('error', 'Retorno Mercado Pago inválido.');
        }

        try {
            $payment = $this->mercadopagoService()->getPaymentInfo($mpPaymentId);
        } catch (RuntimeException $exception) {
            return redirect()->route('checkout.index')->with('error', $exception->getMessage());
        }

        if (($payment['status'] ?? '') !== 'approved') {
            return redirect()->route('checkout.index')->with('error', 'Pagamento Mercado Pago não aprovado.');
        }

        $this->markOrderPaid($order, 'mercadopago', $mpPaymentId);

        session()->forget('cart');

        return redirect()->route('checkout.success', $order);
    }

    public function mercadopagoWebhook(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        try {
            $this->mercadopagoService()->verifyWebhookSignature($request->headers->all(), json_encode($payload));
        } catch (RuntimeException $exception) {
            Log::warning('Webhook Mercado Pago rejeitado', ['error' => $exception->getMessage()]);
            return response()->json(['received' => false], 400);
        }

        $event = $this->mercadopagoService()->parseWebhookEvent($payload);

        if ($event['type'] === 'payment') {
            $orderId = (int) ($event['order_id'] ?? 0);
            $order = $orderId > 0 ? Order::find($orderId) : null;

            if ($order && ($event['status'] ?? '') === 'approved') {
                $this->markOrderPaid($order, 'mercadopago', (string) ($event['payment_id'] ?? ''));
            }
        }

        return response()->json(['received' => true]);
    }

    public function paymentStatus(Order $order): JsonResponse
    {
        if ($order->paid) {
            return response()->json([
                'paid' => true,
                'status' => $order->status,
                'success_url' => route('checkout.success', $order),
            ]);
        }

        $reference = trim((string) $order->payment_reference);
        if ($reference !== '') {
            try {
                $payment = $this->mercadopagoService()->getPaymentInfo($reference);
                if (($payment['status'] ?? '') === 'approved') {
                    $this->markOrderPaid($order, 'mercadopago', $reference);
                    session()->forget('cart');

                    return response()->json([
                        'paid' => true,
                        'status' => 'paid',
                        'success_url' => route('checkout.success', $order),
                    ]);
                }

                if (($payment['status'] ?? '') === 'rejected') {
                    $detail = (string) ($payment['status_detail'] ?? '');

                    $order->update([
                        'status' => 'payment_cancelled',
                    ]);

                    return response()->json([
                        'paid' => false,
                        'final' => true,
                        'status' => 'rejected',
                        'status_detail' => $detail,
                        'message' => $this->cardStatusMessage('rejected', $detail),
                    ]);
                }

                return response()->json([
                    'paid' => false,
                    'final' => false,
                    'status' => $payment['status'] ?? $order->status,
                    'status_detail' => $payment['status_detail'] ?? '',
                    'message' => $this->cardStatusMessage(
                        (string) ($payment['status'] ?? ''),
                        (string) ($payment['status_detail'] ?? '')
                    ),
                ]);
            } catch (RuntimeException $exception) {
                Log::warning('Falha ao consultar status do pagamento', [
                    'order_id' => $order->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'paid' => false,
            'final' => $order->status === 'payment_cancelled',
            'status' => $order->status,
        ]);
    }

    public function success(Order $order): View
    {
        $sessionKey = "pixel_purchase_fired_{$order->id}";
        $pixelFireOnce = ! session()->has($sessionKey);
        session([$sessionKey => true]);

        return view('checkout.success', [
            'order'         => $order->load('items'),
            'cartCount'     => 0,
            'pixelFireOnce' => $pixelFireOnce,
        ]);
    }

    /**
     * Marca o carrinho abandonado como convertido após pagamento confirmado.
     */
    /**
     * Decrementa o estoque dos itens de um pedido após pagamento confirmado.
     */
    private function deductStock(Order $order): void
    {
        $order->load('items');

        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if (! $product || ! ((bool) ($product->track_stock ?? true))) {
                continue;
            }

            $product->decrement('stock', $item->quantity);

            if ($item->size || $item->color) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('size', $item->size ?? '')
                    ->where('color', $item->color ?? '')
                    ->first();

                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                }
            }
        }
    }

    /**
     * Reverte a conversão do carrinho abandonado quando o pagamento é cancelado.
     */
    private function unconvertAbandonedCart(Order $order): void
    {
        AbandonedCart::where('order_id', $order->id)
            ->whereNotNull('converted_at')
            ->update([
                'converted_at' => null,
                'order_id'     => null,
            ]);
    }

    private function convertAbandonedCart(Order $order): void
    {
        // Por session_id (mesmo navegador)
        AbandonedCart::where('session_id', session()->getId())
            ->whereNull('converted_at')
            ->update([
                'converted_at' => now(),
                'order_id'     => $order->id,
            ]);

        // Fallback por e-mail (caso session seja diferente)
        if ($order->customer_email) {
            AbandonedCart::where('customer_email', $order->customer_email)
                ->whereNull('converted_at')
                ->whereNull('order_id')
                ->update([
                    'converted_at' => now(),
                    'order_id'     => $order->id,
                ]);
        }
    }

    private function markOrderPaid(Order $order, string $provider, string $reference): void
    {
        if ($order->paid) {
            return;
        }

        $order->update([
            'paid' => true,
            'status' => 'paid',
            'payment_provider' => $provider,
            'payment_reference' => $reference,
        ]);

        $this->deductStock($order);

        // Marcar carrinho abandonado como convertido somente após pagamento confirmado
        AbandonedCart::where('order_id', $order->id)
            ->whereNull('converted_at')
            ->update(['converted_at' => now()]);

        // Fallback: buscar pelo e-mail do cliente caso order_id não tenha sido vinculado
        if ($order->customer_email) {
            AbandonedCart::where('customer_email', $order->customer_email)
                ->whereNull('converted_at')
                ->whereNull('order_id')
                ->update([
                    'converted_at' => now(),
                    'order_id'     => $order->id,
                ]);
        }
    }

    private function shippingService(): BrazilShipping
    {
        return new BrazilShipping();
    }

    private function mercadopagoService(): MercadoPagoPaymentService
    {
        return new MercadoPagoPaymentService();
    }

    private function startPayment(Order $order, array $data, bool $expectsJson = false): JsonResponse|RedirectResponse
    {
        $paymentMethod = (string) ($data['payment_method'] ?? '');

        try {
            if ($paymentMethod !== 'mercadopago') {
                throw new RuntimeException('Método de pagamento inválido.');
            }

            $paymentType = (string) ($data['payment_type'] ?? 'pix');
            $payer = [
                'document' => $data['customer_document'] ?? $order->customer_document,
            ];

            if ($paymentType === 'pix') {
                $payment = $this->mercadopagoService()->createPixPayment($order, $payer);
                $transactionData = $payment['point_of_interaction']['transaction_data'] ?? [];

                $order->update([
                    'status' => 'awaiting_payment',
                    'payment_reference' => (string) ($payment['id'] ?? ''),
                ]);

                if ($expectsJson) {
                    return response()->json([
                        'payment_type' => 'pix',
                        'status' => $payment['status'] ?? 'pending',
                        'order_id' => $order->id,
                        'payment_id' => (string) ($payment['id'] ?? ''),
                        'qr_code' => $transactionData['qr_code'] ?? '',
                        'qr_code_base64' => $transactionData['qr_code_base64'] ?? '',
                        'ticket_url' => $transactionData['ticket_url'] ?? '',
                        'status_url' => route('checkout.payment.status', $order),
                    ]);
                }

                return redirect()->route('checkout.index')->with('success', 'Pedido criado. Use o Pix exibido na tela para pagar.');
            }

            $payment = $this->mercadopagoService()->createCardPayment($order, [
                'token' => $data['mp_token'] ?? '',
                'payment_method_id' => $data['mp_payment_method_id'] ?? '',
                'issuer_id' => $data['mp_issuer_id'] ?? '',
                'installments' => $data['mp_installments'] ?? 1,
            ], $payer);

            $paymentId = (string) ($payment['id'] ?? '');
            $status = (string) ($payment['status'] ?? '');

            $order->update([
                'status' => $status === 'approved' ? 'paid' : ($status === 'rejected' ? 'payment_cancelled' : 'awaiting_payment'),
                'payment_reference' => $paymentId,
            ]);

            if ($status === 'approved') {
                $this->markOrderPaid($order->fresh(), 'mercadopago', $paymentId);
                session()->forget('cart');
            }

            if ($expectsJson) {
                if ($status === 'approved') {
                    return response()->json([
                        'payment_type' => 'card',
                        'status' => 'approved',
                        'success_url' => route('checkout.success', $order),
                    ]);
                }

                return response()->json([
                    'payment_type' => 'card',
                    'status' => $status,
                    'status_detail' => $payment['status_detail'] ?? '',
                    'message' => $this->cardStatusMessage($status, (string) ($payment['status_detail'] ?? '')),
                    'status_url' => route('checkout.payment.status', $order),
                ], $status === 'rejected' ? 422 : 202);
            }

            if ($status === 'approved') {
                return redirect()->route('checkout.success', $order);
            }

            return redirect()->route('checkout.index')->with('error', $this->cardStatusMessage($status, (string) ($payment['status_detail'] ?? '')));
        } catch (Throwable $exception) {
            Log::error('Falha ao iniciar pagamento', [
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            if ($expectsJson) {
                $message = $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : (config('app.debug') ? $exception->getMessage() : 'Nao foi possivel iniciar o pagamento agora.');

                return response()->json(['error' => $message], 500);
            }

            return redirect()->route('checkout.index')->with('error', $exception->getMessage());
        }
    }

    private function cardStatusMessage(string $status, string $detail): string
    {
        if ($status === 'in_process' || $status === 'pending') {
            return 'Pagamento em analise pelo Mercado Pago. Esta compra ainda nao foi recusada; vamos atualizar automaticamente assim que houver resposta.';
        }

        $messages = [
            'cc_rejected_bad_filled_card_number' => 'Confira o numero do cartao e tente novamente.',
            'cc_rejected_bad_filled_date' => 'Confira a validade do cartao e tente novamente.',
            'cc_rejected_bad_filled_security_code' => 'Confira o codigo de seguranca e tente novamente.',
            'cc_rejected_insufficient_amount' => 'Saldo ou limite insuficiente para concluir a compra.',
            'cc_rejected_high_risk' => 'Pagamento recusado pela analise de seguranca. Tente outro cartao ou Pix.',
            'cc_rejected_blacklist' => 'Pagamento recusado pela operadora. Tente outro cartao ou Pix.',
            'cc_rejected_call_for_authorize' => 'A operadora pediu autorizacao do titular. Entre em contato com o banco ou tente outro cartao.',
            'cc_rejected_card_disabled' => 'Cartao desabilitado. Fale com o banco ou tente outro cartao.',
            'cc_rejected_duplicated_payment' => 'Existe um pagamento parecido em processamento. Aguarde alguns minutos antes de tentar novamente.',
            'cc_rejected_max_attempts' => 'Limite de tentativas atingido. Tente outro cartao ou Pix.',
            'cc_rejected_other_reason' => 'Pagamento recusado. Tente outro cartao ou Pix.',
        ];

        return $messages[$detail] ?? 'Pagamento nao aprovado. Tente novamente ou escolha Pix.';
    }

    private function buildCartSnapshot(array $cart, $products): array
    {
        $items            = [];
        $subtotal         = 0;
        $totalWeightGrams = 0;

        foreach ($cart as $entry) {
            $product = $products->get((int) $entry['product_id']);
            if (! $product) {
                continue;
            }

            $qty       = (int) ($entry['quantity'] ?? 0);
            $lineTotal = $product->price * $qty;
            $subtotal += $lineTotal;
            $totalWeightGrams += ($product->gross_weight_grams ?: ($product->weight_grams ?? 300)) * $qty;

            $items[] = [
                'product'    => $product,
                'quantity'   => $qty,
                'size'       => $entry['size'] ?? null,
                'color'      => $entry['color'] ?? null,
                'line_total' => $lineTotal,
            ];
        }

        return [$items, $subtotal, $totalWeightGrams];
    }

    private function destinationFromData(array $data): array
    {
        return [
            'name'     => $data['customer_name'] ?? 'Cliente',
            'street1'  => $data['shipping_address_line1'] ?? '',
            'street2'  => $data['shipping_address_line2'] ?? null,
            'city'     => $data['shipping_city'] ?? null,
            'postcode' => $data['shipping_postcode'] ?? '',
            'country'  => $data['shipping_country'] ?? 'BR',
            'phone'    => $data['customer_phone'] ?? null,
            'email'    => $data['customer_email'] ?? null,
        ];
    }

    private function formatShippingAddress(array $data): string
    {
        $lines = [
            trim(($data['shipping_address_line1'] ?? '') . ', ' . ($data['shipping_number'] ?? '')),
            $data['shipping_address_line2'] ?? null,
            $data['shipping_neighborhood'] ?? null,
            trim(($data['shipping_city'] ?? '') . ' - ' . ($data['shipping_state'] ?? '')),
            strtoupper($data['shipping_postcode'] ?? ''),
            strtoupper($data['shipping_country'] ?? 'BR'),
        ];

        $lines = array_values(array_filter(array_map(fn ($line) => trim((string) $line), $lines)));

        return implode(PHP_EOL, $lines);
    }

    private function couponDiscount(string $couponCode, float $subtotal): float
    {
        $couponCode = strtoupper(trim($couponCode));

        if ($couponCode === '') {
            return 0.0;
        }

        $coupon = config("store.coupons.{$couponCode}");

        if (! is_array($coupon)) {
            return 0.0;
        }

        $value = max(0, (float) ($coupon['value'] ?? 0));
        $discount = ($coupon['type'] ?? '') === 'percent'
            ? $subtotal * ($value / 100)
            : $value;

        return round(min($subtotal, $discount), 2);
    }

    private function paymentDiscount(string $paymentType, float $subtotal, float $couponDiscount = 0): float
    {
        return $this->pricingService()->paymentDiscount($paymentType, $subtotal, $couponDiscount);
    }

    private function pricingService(): CheckoutPricing
    {
        return app(CheckoutPricing::class);
    }

    private function normalizeCart(): array
    {
        $raw        = session('cart', []);
        $normalized = [];

        foreach ($raw as $k => $v) {
            if (is_array($v)) {
                $normalized[$k] = $v;
            } else {
                $normalized[$k . '__'] = [
                    'product_id' => (int) $k,
                    'quantity'   => (int) $v,
                    'size'       => null,
                    'color'      => null,
                    'variant_id' => null,
                ];
            }
        }

        if ($normalized !== $raw) {
            session(['cart' => $normalized]);
        }

        return $normalized;
    }
}
