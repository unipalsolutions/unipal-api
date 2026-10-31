<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\ProjectController;
use Modules\Projects\Http\Controllers\TaskController;

Route::middleware('auth:sanctum')->prefix('companies/{company}')->scopeBindings()->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/tasks', [TaskController::class, 'store']);
    Route::get('tasks', [TaskController::class, 'index']);
    Route::patch('tasks/{task}', [TaskController::class, 'update']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
});
