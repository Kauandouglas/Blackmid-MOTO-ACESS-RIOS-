<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = $this->normalizeCart();

        $productIds = collect($cart)->pluck('product_id')->unique()->all();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0;

        foreach ($cart as $key => $entry) {
            $product = $products->get((int) $entry['product_id']);

            if (! $product) {
                continue;
            }

            $qty = (int) ($entry['quantity'] ?? 0);
            $lineTotal = $product->price * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                'key'        => $key,
                'product'    => $product,
                'quantity'   => $qty,
                'size'       => $entry['size'] ?? null,
                'color'      => $entry['color'] ?? null,
                'max_quantity' => $this->availableStockForEntry($entry),
                'line_total' => $lineTotal,
            ];
        }

        return view('cart.index', [
            'items'     => $items,
            'subtotal'  => $subtotal,
            'cartCount' => (int) collect($cart)->sum('quantity'),
        ]);
    }

    /** Garante que o carrinho usa a nova estrutura (compatibilidade retroativa). */
    private function normalizeCart(): array
    {
        $raw = session('cart', []);
        $normalized = [];

        foreach ($raw as $k => $v) {
            if (is_array($v)) {
                $normalized[$k] = $v;
            } else {
                // Formato antigo: product_id => quantity
                $key = $k . '__';
                $normalized[$key] = [
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

    public function add(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $hasInventory = (bool) ($product->track_stock ?? true);

        if (! $product->active || ($hasInventory && $product->stock <= 0)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Produto indisponível no momento.'], 422);
            }
            return back()->with('error', 'Produto indisponivel no momento.');
        }

        $sizeOptions = collect($product->sizes ?? [])->filter()->values()->all();
        $colorOptions = collect($product->colors ?? [])->filter()->values()->all();

        $rules = [
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];

        if (!empty($sizeOptions)) {
            $rules['size'] = ['required', 'string', 'in:' . implode(',', $sizeOptions)];
        }

        if (!empty($colorOptions)) {
            $rules['color'] = ['required', 'string', 'in:' . implode(',', $colorOptions)];
        }

        $validated = $request->validate($rules);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $size     = $validated['size']  ?? null;
        $color    = $validated['color'] ?? null;

        $variant = null;
        $hasVariantInventory = false;

        if ($hasInventory) {
            $variant = $this->findVariant($product->id, $size, $color);
            $hasVariantInventory = $variant !== null;

            if ($hasVariantInventory && $variant->stock <= 0) {
                $message = 'Essa variação está esgotada.';

                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return back()->with('error', $message);
            }
        }

        // Chave única por variação: "5_M_Preto"
        $key  = $product->id . '_' . ($size ?? '') . '_' . ($color ?? '');
        $cart = $this->normalizeCart();

        $currentQty = isset($cart[$key]) ? (int) ($cart[$key]['quantity'] ?? 0) : 0;
        $targetQty = $currentQty + $quantity;

        if ($hasInventory) {
            $availableStock = $hasVariantInventory
                ? (int) $variant->stock
                : (int) $product->stock;

            $targetQty = min($availableStock, $targetQty);
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'quantity'   => $targetQty,
            'size'       => $size,
            'color'      => $color,
            'variant_id' => $variant?->id,
        ];

        session(['cart' => $cart]);

        $cartCount = (int) collect($cart)->sum('quantity');

        if ($request->wantsJson()) {
            return response()->json([
                'success'       => true,
                'cart_count'    => $cartCount,
                'product_name'  => $product->name,
                'product_image' => $product->image,
                'product_price' => 'R$ ' . number_format((float) $product->price, 2, ',', '.'),
                'cart_url'      => route('cart.index'),
            ]);
        }

        return back()->with('success', 'Produto adicionado ao carrinho.');
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            $validated = $request->validate([
                'key'      => ['required', 'string', 'max:200'],
                'quantity' => ['required', 'integer', 'min:0', 'max:99'],
            ]);

            $key  = $validated['key'];
            $qty  = (int) $validated['quantity'];
            $cart = $this->normalizeCart();

            if (! array_key_exists($key, $cart)) {
                return response()->json(['success' => false, 'message' => 'Item não encontrado.'], 404);
            }

            if ($qty <= 0) {
                unset($cart[$key]);
                $updatedKey = null;
            } else {
                $availableStock = $this->availableStockForEntry($cart[$key]);
                if ($availableStock !== null) {
                    $qty = min($qty, $availableStock);
                }

                if ($qty <= 0) {
                    unset($cart[$key]);
                    $updatedKey = null;
                    session(['cart' => $cart]);

                    return $this->cartJsonSummary($cart, null);
                }

                $cart[$key]['quantity'] = $qty;
                $updatedKey = $key;
            }

            session(['cart' => $cart]);
            return $this->cartJsonSummary($cart, $updatedKey);
        }

        // Fallback form-based
        $validated = $request->validate([
            'quantities'   => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $cart = $this->normalizeCart();

        foreach ($validated['quantities'] as $key => $newQty) {
            $newQty = (int) $newQty;
            if ($newQty <= 0) {
                unset($cart[$key]);
            } elseif (isset($cart[$key])) {
                $availableStock = $this->availableStockForEntry($cart[$key]);
                if ($availableStock !== null) {
                    $newQty = min($newQty, $availableStock);
                }

                if ($newQty <= 0) {
                    unset($cart[$key]);
                    continue;
                }

                $cart[$key]['quantity'] = $newQty;
            }
        }

        session(['cart' => $cart]);
        return redirect()->route('cart.index')->with('success', 'Carrinho atualizado.');
    }

    public function remove(Request $request): RedirectResponse|JsonResponse
    {
        $key  = $request->input('key', '');
        $cart = $this->normalizeCart();

        if ($key !== '' && array_key_exists($key, $cart)) {
            unset($cart[$key]);
        }

        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return $this->cartJsonSummary($cart);
        }

        return redirect()->route('cart.index')->with('success', 'Item removido do carrinho.');
    }

    private function cartJsonSummary(array $cart, ?string $updatedKey = null): JsonResponse
    {
        $productIds = collect($cart)->pluck('product_id')->unique()->filter()->all();
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $subtotal        = 0;
        $updatedItemData = null;

        foreach ($cart as $key => $entry) {
            $product = $products->get((int) $entry['product_id']);
            if (! $product) continue;
            $qty       = (int) ($entry['quantity'] ?? 0);
            $lineTotal = $product->price * $qty;
            $subtotal += $lineTotal;

            if ($key === $updatedKey) {
                $availableStock = $this->availableStockForEntry($entry);
                $updatedItemData = [
                    'key'                  => $key,
                    'quantity'             => $qty,
                    'available_stock'      => $availableStock,
                    'line_total'           => $lineTotal,
                    'line_total_formatted' => 'R$ ' . number_format($lineTotal, 2, ',', '.'),
                ];
            }
        }

        $shipFee   = config('store.shipping_fee');
        $shipping  = $shipFee;
        $total     = $subtotal + $shipping;
        $cartCount = (int) collect($cart)->sum('quantity');

        return response()->json([
            'success'            => true,
            'cart_count'         => $cartCount,
            'subtotal'           => $subtotal,
            'subtotal_formatted' => 'R$ ' . number_format($subtotal, 2, ',', '.'),
            'shipping'           => $shipping,
            'shipping_formatted' => $shipping > 0 ? 'R$ ' . number_format($shipping, 2, ',', '.') : 'Gratis',
            'total'              => $total,
            'total_formatted'    => 'R$ ' . number_format($total, 2, ',', '.'),
            'updated_item'       => $updatedItemData,
        ]);
    }

    public function clear(): RedirectResponse
    {
        session()->forget('cart');

        return redirect()->route('cart.index')->with('success', 'Carrinho limpo com sucesso.');
    }

    private function findVariant(int $productId, ?string $size, ?string $color): ?ProductVariant
    {
        $size = trim((string) ($size ?? ''));
        $color = trim((string) ($color ?? ''));

        return ProductVariant::query()
            ->where('product_id', $productId)
            ->where('size', $size)
            ->where('color', $color)
            ->first();
    }

    private function availableStockForEntry(array $entry): ?int
    {
        $productId = (int) ($entry['product_id'] ?? 0);
        $product = $productId > 0 ? Product::find($productId) : null;

        if (! $product || ! $product->active) {
            return 0;
        }

        $hasInventory = (bool) ($product->track_stock ?? true);
        if (! $hasInventory) {
            return null;
        }

        $variant = $this->findVariant($product->id, $entry['size'] ?? null, $entry['color'] ?? null);

        return $variant ? (int) $variant->stock : (int) $product->stock;
    }
}
