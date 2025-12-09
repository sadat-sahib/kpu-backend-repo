<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-db', function() {
    try {
        \DB::connection()->getPdo();
        return 'DB connected!';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
Route::get('/', function () {
    return view('welcome');
});
use Illuminate\Support\Facades\Http;

Route::get('/test-recaptcha', function () {
    $secret = env('RECAPTCHA_SECRET_KEY');
    if (!$secret) {
        return response('❌ Secret key missing in .env', 500);
    }

    try {
        $response = Http::timeout(10)->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => 'test-token',
            'remoteip' => '127.0.0.1'
        ]);

        return response()->json([
            'status' => '✅ Connection OK',
            'google_response' => $response->json()
        ]);
    } catch (\Exception $e) {
        return response('❌ Connection failed: ' . $e->getMessage(), 500);
    }
});
