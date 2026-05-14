<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBlingAuthController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminBlogPostController;
use App\Http\Controllers\Admin\AdminBlingProductImportController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\AdminMenuItemController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminAbandonedCartController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminSettingController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store.index');
Route::get('/product/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('/busca/sugestoes', [StoreController::class, 'searchSuggestions'])->name('store.search.suggestions');
Route::get('/busca', [StoreController::class, 'search'])->name('store.search');
Route::get('/blog', [StoreController::class, 'blog'])->name('store.blog');
Route::get('/blog/{slug}', [StoreController::class, 'blogShow'])->name('store.blog.show');
Route::get('/sobre-nos', fn () => view('store.sobre'))->name('store.sobre');
Route::get('/politica-de-privacidade', fn () => view('store.politica-privacidade'))->name('store.privacidade');
Route::get('/trocas-e-devolucoes', fn () => view('store.trocas-devolucoes'))->name('store.trocas');
Route::redirect('/trocas-e-devolucoesm', '/trocas-e-devolucoes', 301);
Route::get('/fale-conosco', fn () => view('store.fale-conosco'))->name('store.contato');
Route::get('/minha-conta', [CustomerAuthController::class, 'account'])->name('store.minha-conta');

// ── Autenticação de clientes ──────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/entrar', [CustomerAuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/entrar', [CustomerAuthController::class, 'login'])->name('auth.login.submit');
    Route::get('/cadastrar', [CustomerAuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/cadastrar', [CustomerAuthController::class, 'register'])->name('auth.register.submit');
});
Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('auth.logout');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/quote', [CheckoutController::class, 'quote'])->name('checkout.quote');
Route::post('/checkout/capture-cart', [CheckoutController::class, 'captureCart'])->name('checkout.capture-cart');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/payment/mercadopago/success/{order}', [CheckoutController::class, 'mercadopagoSuccess'])->name('checkout.payment.mercadopago.success');
Route::get('/checkout/payment/mercadopago/pending/{order}', [CheckoutController::class, 'paymentCancel'])->name('checkout.payment.mercadopago.pending');
Route::get('/checkout/payment/cancel/{order}', [CheckoutController::class, 'paymentCancel'])->name('checkout.payment.cancel');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::post('/webhooks/mercadopago', [CheckoutController::class, 'mercadopagoWebhook'])
	->withoutMiddleware([VerifyCsrfToken::class])
	->name('webhooks.mercadopago');

Route::prefix('admin')->name('admin.')->group(function () {
	Route::middleware('guest')->group(function () {
		Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
		Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
	});

	Route::middleware(['auth', 'admin'])->group(function () {
		Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

		Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

		Route::resource('categorias', AdminCategoryController::class)->except('show');
		Route::resource('blogs', AdminBlogPostController::class)->except('show');
		Route::post('/blogs/upload-image', [AdminBlogPostController::class, 'uploadImage'])->name('blogs.upload-image');
		Route::get('/bling/conectar', [AdminBlingAuthController::class, 'show'])->name('bling.auth');
		Route::post('/bling/conectar', [AdminBlingAuthController::class, 'connect'])->name('bling.connect');
		Route::get('/bling/callback', [AdminBlingAuthController::class, 'callback'])->name('bling.callback');
		Route::get('/bling/produtos', [AdminBlingProductImportController::class, 'index'])->name('bling.products.index');
		Route::post('/bling/produtos/importar', [AdminBlingProductImportController::class, 'import'])->name('bling.products.import');
		Route::resource('produtos', AdminProductController::class)->except('show');
		Route::resource('menus', AdminMenuController::class)->except('show');

		Route::get('/pedidos', [AdminOrderController::class, 'index'])->name('orders.index');
		Route::get('/pedidos/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
		Route::patch('/pedidos/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
		Route::post('/menus/{menu}/itens', [AdminMenuItemController::class, 'store'])->name('menu-items.store');
		Route::post('/menus/{menu}/itens/reorder', [AdminMenuItemController::class, 'reorder'])->name('menu-items.reorder');
		Route::put('/menu-itens/{menuItem}', [AdminMenuItemController::class, 'update'])->name('menu-items.update');
		Route::delete('/menu-itens/{menuItem}', [AdminMenuItemController::class, 'destroy'])->name('menu-items.destroy');

		Route::get('/carrinhos-abandonados', [AdminAbandonedCartController::class, 'index'])->name('abandoned-carts.index');
		Route::get('/carrinhos-abandonados/{abandonedCart}', [AdminAbandonedCartController::class, 'show'])->name('abandoned-carts.show');

		Route::get('/configuracoes', [AdminSettingController::class, 'edit'])->name('settings.edit');
		Route::put('/configuracoes', [AdminSettingController::class, 'update'])->name('settings.update');
		Route::get('/pixels-marketing', [AdminSettingController::class, 'editPixels'])->name('pixel-marketing.edit');
		Route::put('/pixels-marketing', [AdminSettingController::class, 'updatePixels'])->name('pixel-marketing.update');
		Route::get('/pagamentos', [AdminPaymentController::class, 'edit'])->name('payments.edit');
		Route::put('/pagamentos', [AdminPaymentController::class, 'update'])->name('payments.update');
	});
});

// Rota de categoria — deve ficar por último para não conflitar
Route::get('/categoria/{slug}', [StoreController::class, 'category'])->name('store.category');

Route::get('/{slug}', function (string $slug) {
	$exists = \App\Models\Category::query()
		->where('slug', $slug)
		->where('active', true)
		->exists();

	abort_unless($exists, 404);

	return redirect()->route('store.category', ['slug' => $slug], 301);
});
