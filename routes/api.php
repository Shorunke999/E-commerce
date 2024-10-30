<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/stripe_webhooks',function(Request $request){
    Log::info('request body of webhook is',[
        $request->all()
    ]);
});

Route::get('/product/checkout',function(Request $request){
    $request->validate([
        'cart' => 'array|required',
    ]);
    $total_price = 0;
    foreach($request->cart as $product){
        $total_price_of_product = $product['number'] * $product['price'];
        $total_price += $total_price_of_product;
    }
    $user = Auth::user();
    if ($user && $total_price>0){
        $request->user()->checkout($total_price * 100,'usd',[
            'success_url' => url('/checkout/success'),
            'cancel_url' => url('/checkout/cancel'),
        ]);
    }
    return response()->json(['message'=>'User is not Authenticated or Total price cant be Zero'],400);
});
