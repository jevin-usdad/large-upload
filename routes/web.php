<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LargeUploadController;

Route::get('/', function () {
    return view('s3-upload');
});

Route::post('/upload/initiate', [LargeUploadController::class, 'initiate']);
Route::post('/upload/presign', [LargeUploadController::class, 'getPresignedUrl']);
Route::post('/upload/complete', [LargeUploadController::class, 'complete']);

