<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PasienController;

Route::apiResource('pasiens', PasienController::class);
Route::get('/pasiens/export/excel', [PasienController::class, 'exportExcel']);
Route::get('/pasiens/export/pdf', [PasienController::class, 'exportPDF']);
