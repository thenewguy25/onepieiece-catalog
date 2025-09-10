<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CharacterController;

Route::get('/', [CharacterController::class, 'index']);
Route::get('/characters', [CharacterController::class, 'index'])->name('characters.index');
Route::post('/characters/search', [CharacterController::class, 'index'])->name('characters.search');

Route::resource('characters', CharacterController::class)->except(['index']);