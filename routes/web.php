<?php

use App\Http\Controllers\EngagementPlannerController;
use App\Http\Controllers\SseController;
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
Route::get('/healthz', function () {
    return response('ok', 200);
})->name('healthz');

Route::get('/guests', [EngagementPlannerController::class, 'guestsPage'])->name('guests.index');
Route::post('/guests', [EngagementPlannerController::class, 'storeGuest'])->name('guests.store');
Route::post('/guests/bulk', [EngagementPlannerController::class, 'storeGuestsBulk'])->name('guests.bulk-store');
Route::post('/guests/bulk-delete', [EngagementPlannerController::class, 'destroyGuestsBulk'])->name('guests.bulk-destroy');
Route::post('/guests/reorder', [EngagementPlannerController::class, 'reorderGuests'])->name('guests.reorder');
Route::put('/guests/{guest}', [EngagementPlannerController::class, 'updateGuest'])->name('guests.update');
Route::delete('/guests/{guest}', [EngagementPlannerController::class, 'destroyGuest'])->name('guests.destroy');

Route::get('/tasks', [EngagementPlannerController::class, 'tasksPage'])->name('tasks.index');
Route::post('/tasks', [EngagementPlannerController::class, 'storeTask'])->name('tasks.store');
Route::post('/tasks/bulk', [EngagementPlannerController::class, 'storeTasksBulk'])->name('tasks.bulk-store');
Route::post('/tasks/bulk-delete', [EngagementPlannerController::class, 'destroyTasksBulk'])->name('tasks.bulk-destroy');
Route::put('/tasks/{task}', [EngagementPlannerController::class, 'updateTask'])->name('tasks.update');
Route::delete('/tasks/{task}', [EngagementPlannerController::class, 'destroyTask'])->name('tasks.destroy');

Route::get('/vendors', [EngagementPlannerController::class, 'vendorsPage'])->name('vendors.index');
Route::post('/vendors', [EngagementPlannerController::class, 'storeVendor'])->name('vendors.store');
Route::post('/vendors/bulk', [EngagementPlannerController::class, 'storeVendorsBulk'])->name('vendors.bulk-store');
Route::post('/vendors/bulk-delete', [EngagementPlannerController::class, 'destroyVendorsBulk'])->name('vendors.bulk-destroy');
Route::put('/vendors/{vendor}', [EngagementPlannerController::class, 'updateVendor'])->name('vendors.update');
Route::delete('/vendors/{vendor}', [EngagementPlannerController::class, 'destroyVendor'])->name('vendors.destroy');

Route::get('/gifts', [EngagementPlannerController::class, 'giftsPage'])->name('gifts.index');
Route::post('/gifts', [EngagementPlannerController::class, 'storeGift'])->name('gifts.store');
Route::post('/gifts/bulk', [EngagementPlannerController::class, 'storeGiftsBulk'])->name('gifts.bulk-store');
Route::post('/gifts/bulk-delete', [EngagementPlannerController::class, 'destroyGiftsBulk'])->name('gifts.bulk-destroy');
Route::post('/gifts/reorder', [EngagementPlannerController::class, 'reorderGifts'])->name('gifts.reorder');
Route::post('/gifts/reorder-groups', [EngagementPlannerController::class, 'reorderGiftGroups'])->name('gifts.reorder-groups');
Route::put('/gifts/{gift}', [EngagementPlannerController::class, 'updateGift'])->name('gifts.update');
Route::delete('/gifts/{gift}', [EngagementPlannerController::class, 'destroyGift'])->name('gifts.destroy');

Route::get('/expenses', [EngagementPlannerController::class, 'expensesPage'])->name('expenses.index');
Route::post('/expenses', [EngagementPlannerController::class, 'storeExpense'])->name('expenses.store');
Route::post('/expenses/bulk', [EngagementPlannerController::class, 'storeExpensesBulk'])->name('expenses.bulk-store');
Route::post('/expenses/bulk-delete', [EngagementPlannerController::class, 'destroyExpensesBulk'])->name('expenses.bulk-destroy');
Route::put('/expenses/{expense}', [EngagementPlannerController::class, 'updateExpense'])->name('expenses.update');
Route::delete('/expenses/{expense}', [EngagementPlannerController::class, 'destroyExpense'])->name('expenses.destroy');

if (!config('app.disable_sse')) {
    Route::get('/events', [SseController::class, 'stream'])
        ->name('events.stream')
        ->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);
}
