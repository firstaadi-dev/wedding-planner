<?php

use App\Http\Controllers\Auth\WorkosAuthController;
use App\Http\Controllers\EngagementPlannerController;
use App\Http\Controllers\SseController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('engagement.index')
        : redirect()->route('login');
});

Route::get('/healthz', function () {
    return response('ok', 200);
})->name('healthz');

Route::middleware('guest')->group(function () {
    Route::get('/register', [WorkosAuthController::class, 'register'])->name('register');
    Route::get('/login', [WorkosAuthController::class, 'login'])->name('login');
    Route::get('/auth/workos/callback', [WorkosAuthController::class, 'callback'])->name('workos.callback');
    Route::get('/auth/workos/failed', [WorkosAuthController::class, 'failed'])->name('workos.failed');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WorkosAuthController::class, 'logout'])->name('logout');

    Route::get('/workspace/invitations/{token}/accept', [WorkspaceController::class, 'acceptInvitation'])
        ->name('workspace.invitations.accept');
});

Route::middleware(['auth', 'workspace.access'])->group(function () {
    Route::get('/app', [EngagementPlannerController::class, 'index'])->name('engagement.index');

    Route::post('/workspace/mode', [WorkspaceController::class, 'switchMode'])->name('workspace.mode.switch');
    Route::post('/workspace/invitations', [WorkspaceController::class, 'invitePartner'])->name('workspace.invitations.store');
    Route::post('/billing/sandbox/upgrade', [WorkspaceController::class, 'upgradeToProSandbox'])->name('billing.sandbox.upgrade');

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
    Route::post('/vendors/reorder-groups', [EngagementPlannerController::class, 'reorderVendorGroups'])->name('vendors.reorder-groups');
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
        Route::get('/events', [SseController::class, 'stream'])->name('events.stream');
    }
});
