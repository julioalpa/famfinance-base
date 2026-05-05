<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\FamilyGroupController;
use App\Http\Controllers\MonthlyPaymentController;
use App\Http\Controllers\PaymentItemController;
use App\Http\Controllers\CardPaymentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  fn() => view('auth.login'))->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/switch',   [GoogleAuthController::class, 'redirectSwitch'])->name('auth.google.switch');
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

    // Contraseña (no requiere grupo activo)
    Route::get('/perfil/password',  [AuthController::class, 'showPassword'])->name('profile.password');
    Route::post('/perfil/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');

    // Setup de grupo familiar (sin requerir grupo activo)
    Route::get('/setup', [FamilyGroupController::class, 'setup'])->name('family-groups.setup');
    Route::post('/grupos', [FamilyGroupController::class, 'store'])->name('family-groups.store');

    // Todo lo demás requiere tener un grupo activo
    Route::middleware(\App\Http\Middleware\EnsureUserBelongsToGroup::class)->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Reportes
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reportes/balance-mensual', [MonthlyReportController::class, 'index'])->name('reports.monthly');
        Route::post('/reportes/balance-mensual/pdf', [MonthlyReportController::class, 'pdf'])->name('reports.monthly.pdf');

        // Préstamos — plan de cuotas
        Route::get('/prestamos/{account}/plan',                        [LoanController::class, 'setup'])->name('loans.setup');
        Route::post('/prestamos/{account}/plan',                       [LoanController::class, 'storeSchedule'])->name('loans.store-schedule');
        Route::delete('/prestamos/{account}/plan',                     [LoanController::class, 'destroySchedule'])->name('loans.destroy-schedule');
        Route::post('/prestamos/cuotas/{loanInstallment}/pagar',       [LoanController::class, 'pay'])->name('loans.pay');

        // Cuentas
        Route::post('/cuentas/{account}/ajustar', [AccountController::class, 'adjust'])->name('accounts.adjust');
        Route::resource('cuentas', AccountController::class)->names([
            'index'   => 'accounts.index',
            'create'  => 'accounts.create',
            'store'   => 'accounts.store',
            'show'    => 'accounts.show',
            'edit'    => 'accounts.edit',
            'update'  => 'accounts.update',
            'destroy' => 'accounts.destroy',
        ])->parameters(['cuentas' => 'account']);

        // Pago de tarjeta (antes del resource para no colisionar con /{transaction})
        Route::get('/movimientos/pago-tarjeta',  [CardPaymentController::class, 'create'])->name('card-payment.create');
        Route::post('/movimientos/pago-tarjeta', [CardPaymentController::class, 'store'])->name('card-payment.store');

        // Movimientos (gastos e ingresos)
        Route::resource('movimientos', TransactionController::class)->names([
            'index'   => 'transactions.index',
            'create'  => 'transactions.create',
            'store'   => 'transactions.store',
            'show'    => 'transactions.show',
            'edit'    => 'transactions.edit',
            'update'  => 'transactions.update',
            'destroy' => 'transactions.destroy',
        ])->parameters(['movimientos' => 'transaction']);

        // Grupo familiar
        Route::get('/grupo', [FamilyGroupController::class, 'show'])
            ->name('family-groups.show');
        Route::post('/grupo/invitar', [FamilyGroupController::class, 'invite'])
            ->name('family-groups.invite');
        Route::delete('/grupo/miembros/{userId}', [FamilyGroupController::class, 'removeMember'])
            ->name('family-groups.remove-member');
        Route::delete('/grupo/invitaciones/{invitation}', [FamilyGroupController::class, 'revokeInvitation'])
            ->name('family-groups.revoke-invitation');
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
            ])->parameters(['categorias' => 'category']);

        // Tipo de cambio
        Route::get('/tipo-de-cambio',           [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
        Route::post('/tipo-de-cambio',           [ExchangeRateController::class, 'store'])->name('exchange-rates.store');
        Route::delete('/tipo-de-cambio/{exchangeRate}', [ExchangeRateController::class, 'destroy'])->name('exchange-rates.destroy');

        // Redirect de rutas antiguas de gastos recurrentes
        Route::get('/debitos', fn() => redirect()->route('monthly-payments.index'))->name('recurring-expenses.index');
        Route::get('/debitos/crear', fn() => redirect()->route('payment-items.create'))->name('recurring-expenses.create');

        // Checklist mensual de pendientes
        Route::get('/pendientes', [MonthlyPaymentController::class, 'index'])->name('monthly-payments.index');
        Route::post('/pendientes/{monthlyPayment}/confirmar',  [MonthlyPaymentController::class, 'confirm'])->name('monthly-payments.confirm');
        Route::post('/pendientes/{monthlyPayment}/pagar',      [MonthlyPaymentController::class, 'markPaid'])->name('monthly-payments.mark-paid');
        Route::post('/pendientes/{monthlyPayment}/desmarcar',  [MonthlyPaymentController::class, 'markUnpaid'])->name('monthly-payments.mark-unpaid');
        Route::post('/pendientes/{monthlyPayment}/descartar',  [MonthlyPaymentController::class, 'dismiss'])->name('monthly-payments.dismiss');
        Route::post('/pendientes/{monthlyPayment}/restaurar',  [MonthlyPaymentController::class, 'undismiss'])->name('monthly-payments.undismiss');

        // Etiquetas
        Route::get('/etiquetas',          [TagController::class, 'index'])->name('tags.index');
        Route::post('/etiquetas',         [TagController::class, 'store'])->name('tags.store');
        Route::put('/etiquetas/{tag}',    [TagController::class, 'update'])->name('tags.update');
        Route::delete('/etiquetas/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

        // Ítems de pendientes (plantillas)
        Route::post('/pendientes-items/{paymentItem}/toggle',  [PaymentItemController::class, 'toggle'])->name('payment-items.toggle');
        Route::post('/pendientes-items/{paymentItem}/retirar', [PaymentItemController::class, 'retire'])->name('payment-items.retire');
        Route::resource('pendientes-items', PaymentItemController::class)->names([
            'index'   => 'payment-items.index',
            'create'  => 'payment-items.create',
            'store'   => 'payment-items.store',
            'edit'    => 'payment-items.edit',
            'update'  => 'payment-items.update',
            'destroy' => 'payment-items.destroy',
        ])->except(['show'])->parameters(['pendientes-items' => 'paymentItem']);
    });
});
