<?php

use App\Http\Controllers\EngagementPlannerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [EngagementPlannerController::class, 'index'])->name('engagement.index');

Route::get('/guests', [EngagementPlannerController::class, 'guestsPage'])->name('guests.index');
Route::post('/guests', [EngagementPlannerController::class, 'storeGuest'])->name('guests.store');
Route::post('/guests/reorder', [EngagementPlannerController::class, 'reorderGuests'])->name('guests.reorder');
Route::put('/guests/{guest}', [EngagementPlannerController::class, 'updateGuest'])->name('guests.update');
Route::delete('/guests/{guest}', [EngagementPlannerController::class, 'destroyGuest'])->name('guests.destroy');

Route::get('/tasks', [EngagementPlannerController::class, 'tasksPage'])->name('tasks.index');
Route::post('/tasks', [EngagementPlannerController::class, 'storeTask'])->name('tasks.store');
Route::put('/tasks/{task}', [EngagementPlannerController::class, 'updateTask'])->name('tasks.update');
Route::delete('/tasks/{task}', [EngagementPlannerController::class, 'destroyTask'])->name('tasks.destroy');

Route::get('/gifts', [EngagementPlannerController::class, 'giftsPage'])->name('gifts.index');
Route::post('/gifts', [EngagementPlannerController::class, 'storeGift'])->name('gifts.store');
Route::put('/gifts/{gift}', [EngagementPlannerController::class, 'updateGift'])->name('gifts.update');
Route::delete('/gifts/{gift}', [EngagementPlannerController::class, 'destroyGift'])->name('gifts.destroy');

Route::get('/expenses', [EngagementPlannerController::class, 'expensesPage'])->name('expenses.index');
Route::post('/expenses', [EngagementPlannerController::class, 'storeExpense'])->name('expenses.store');
Route::put('/expenses/{expense}', [EngagementPlannerController::class, 'updateExpense'])->name('expenses.update');
Route::delete('/expenses/{expense}', [EngagementPlannerController::class, 'destroyExpense'])->name('expenses.destroy');
