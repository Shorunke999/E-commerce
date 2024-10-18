<?php

use Illuminate\Http\Request;
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
