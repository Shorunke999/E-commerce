<?php

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Cashier;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/checkout/success', function (Request $request) {
    $sessionId = $request->get('session_id');

    if ($sessionId === null) {
        return;
    }

    $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

    if ($session->payment_status !== 'paid') {
        //return;
        return view('dashboard')->with('msg','Payment not Successfull');
    }

    $orderId = $session['metadata']['order_id'] ?? null;

    $order = Order::findOrFail($orderId);

    $order->update(['status' => 'completed']);

    return view('checkout-success', ['order' => $order]);
})->name('checkout-success');

// Checkout initiation route
Route::get('/product/checkout', function (Request $request) {
    $request->validate(['cart' => 'array|required']);

    $total_price = 0;
    foreach ($request->cart as $product) {
        $total_price += $product['number'] * $product['price'];
    }

    if ($total_price <= 0 || !$request->user()) {
        return response()->json(['message' => 'User is not authenticated or total price is zero.'], 400);
    }

    $order = Order::create([
        'user_id' => $request->user()->id,
        'status' => 'pending',
        'meta_data' => json_encode($request->cart)
    ]);

    return $request->user()->checkout($total_price * 100, 'usd', [
        'success_url' => url('/checkout/success?session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => url('/checkout/cancel'),
        'metadata' => ['order_id' => $order->id],
    ]);
})->name('checkout');


