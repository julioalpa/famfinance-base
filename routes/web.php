<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\FamilyGroupController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ── Auth: Google OAuth ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

// ── Invitación pública (no requiere auth, la maneja internamente) ─────────────
Route::get('/invitacion/{token}', [FamilyGroupController::class, 'acceptInvitation'])
    ->name('invitations.accept');

// ── Área autenticada ──────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Setup de grupo familiar (sin requerir grupo activo)
    Route::get('/setup', [FamilyGroupController::class, 'setup'])->name('family-groups.setup');
    Route::post('/grupos', [FamilyGroupController::class, 'store'])->name('family-groups.store');

    // Todo lo demás requiere tener un grupo activo
    Route::middleware(\App\Http\Middleware\EnsureUserBelongsToGroup::class)->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Reportes
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');

        // Cuentas
        Route::resource('cuentas', AccountController::class)->names([
            'index'   => 'accounts.index',
            'create'  => 'accounts.create',
            'store'   => 'accounts.store',
            'show'    => 'accounts.show',
            'edit'    => 'accounts.edit',
            'update'  => 'accounts.update',
            'destroy' => 'accounts.destroy',
        ]);

        // Movimientos (gastos e ingresos)
        Route::resource('movimientos', TransactionController::class)->names([
            'index'   => 'transactions.index',
            'create'  => 'transactions.create',
            'store'   => 'transactions.store',
            'show'    => 'transactions.show',
            'edit'    => 'transactions.edit',
            'update'  => 'transactions.update',
            'destroy' => 'transactions.destroy',
        ]);

        // Grupo familiar
        Route::get('/grupo', [FamilyGroupController::class, 'show'])
            ->name('family-groups.show');
        Route::post('/grupo/invitar', [FamilyGroupController::class, 'invite'])
            ->name('family-groups.invite');
        Route::delete('/grupo/miembros/{userId}', [FamilyGroupController::class, 'removeMember'])
            ->name('family-groups.remove-member');
        Route::post('/grupo/cambiar/{familyGroup}', [FamilyGroupController::class, 'switchGroup'])
            ->name('family-groups.switch');

        // Importar CSV
        Route::get('/importar',  [ImportController::class, 'index'])->name('import.index');
        Route::post('/importar', [ImportController::class, 'store'])->name('import.store');

        // Categorías
        Route::resource('categorias', CategoryController::class)
            ->except(['show'])
            ->names([
                'index'   => 'categories.index',
                'create'  => 'categories.create',
                'store'   => 'categories.store',
                'edit'    => 'categories.edit',
                'update'  => 'categories.update',
                'destroy' => 'categories.destroy',
            ]);

        // Tipo de cambio
        Route::get('/tipo-de-cambio',           [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
        Route::post('/tipo-de-cambio',           [ExchangeRateController::class, 'store'])->name('exchange-rates.store');
        Route::delete('/tipo-de-cambio/{exchangeRate}', [ExchangeRateController::class, 'destroy'])->name('exchange-rates.destroy');

        // Débitos fijos / gastos recurrentes
        Route::post('/debitos/{recurringExpense}/toggle', [RecurringExpenseController::class, 'toggle'])->name('recurring-expenses.toggle');
        Route::resource('debitos', RecurringExpenseController::class)->names([
            'index'   => 'recurring-expenses.index',
            'create'  => 'recurring-expenses.create',
            'store'   => 'recurring-expenses.store',
            'edit'    => 'recurring-expenses.edit',
            'update'  => 'recurring-expenses.update',
            'destroy' => 'recurring-expenses.destroy',
        ])->except(['show']);
    });
});
