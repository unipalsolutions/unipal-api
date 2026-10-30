<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Http\Controllers\LeadActivityController;
use Modules\Crm\Http\Controllers\LeadController;
use Modules\Crm\Http\Controllers\PipelineController;
use Modules\Crm\Http\Controllers\PublicLeadController;

Route::post('public/leads', [PublicLeadController::class, 'store'])->middleware('throttle:public-leads');

Route::middleware('auth:sanctum')->prefix('companies/{company}')->scopeBindings()->group(function () {
    Route::get('pipeline', PipelineController::class);
    Route::apiResource('leads', LeadController::class);
    Route::post('leads/{lead}/activities', [LeadActivityController::class, 'store']);
});
