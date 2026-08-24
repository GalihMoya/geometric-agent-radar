<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\AgentController;

Route::get('/', [AgentController::class, 'index'])->name('radar.index');
Route::get('/api/agents', [AgentController::class, 'getAgents'])->name('api.agents');
