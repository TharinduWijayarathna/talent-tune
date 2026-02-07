<?php

use App\Http\Controllers\Viva\GeminiController;
use App\Http\Controllers\Viva\TTSController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Generation / AI API Routes (Viva TTS, Gemini)
|--------------------------------------------------------------------------
*/
Route::post('api/viva/tts', [TTSController::class, 'generate'])
    ->middleware(['auth'])
    ->name('viva.tts');

Route::prefix('api/viva')->middleware(['auth'])->group(function () {
    Route::post('questions/generate', [GeminiController::class, 'generateQuestions'])
        ->name('viva.questions.generate');

    Route::post('answer/evaluate', [GeminiController::class, 'evaluateAnswer'])
        ->name('viva.answer.evaluate');

    Route::post('conversation/response', [GeminiController::class, 'generateConversationalResponse'])
        ->name('viva.conversation.response');
});
