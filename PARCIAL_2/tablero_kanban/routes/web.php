<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'kanban');
Route::apiResource('tasks', TaskController::class)->except('show');
