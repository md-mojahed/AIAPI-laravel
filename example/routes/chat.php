<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// Copy these into routes/api.php (or web.php if you prefer sessions/csrf).

Route::prefix('chat')->group(function () {
    Route::post('/', [ChatController::class, 'store'])->name('chat.store');
    Route::get('{chat}/messages', [ChatController::class, 'history'])->name('chat.history');
    Route::post('{chat}/ask', [ChatController::class, 'ask'])->name('chat.ask');
    Route::delete('{chat}', [ChatController::class, 'destroy'])->name('chat.destroy');
});
