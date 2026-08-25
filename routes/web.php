<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AgentController::class, 'index'])->name('radar.index');
Route::get('/api/agents', [AgentController::class, 'getAgents'])->name('api.agents');
Route::get('/api/hq', [AgentController::class, 'getHqLocations'])->name('api.hq');
