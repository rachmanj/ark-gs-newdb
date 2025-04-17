<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetTypeController;
use App\Http\Controllers\DashboardDailyController;
use App\Http\Controllers\DashboardMonthlyController;
use App\Http\Controllers\DashboardOtherController;
use App\Http\Controllers\DashboardYearlyController;
use App\Http\Controllers\GrpoController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\IncomingController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MigiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PowithetaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\POController;
use App\Http\Controllers\DailyProductionController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('authenticate');

    Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    // Redirect root to dashboard daily
    Route::get('/', function () {
        return redirect()->route('dashboard.daily.index');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('data', [UserController::class, 'data'])->name('data');
        Route::get('change-password/{id}', [UserController::class, 'change_password'])->name('change_password');
        Route::put('password-update/{id}', [UserController::class, 'password_update'])->name('password_update');
    });
    Route::resource('users', UserController::class);

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RoleController::class, 'update'])->name('update');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
    });

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/daily', [DashboardDailyController::class, 'index'])->name('daily.index');

        Route::get('/monthly', [DashboardMonthlyController::class, 'index'])->name('monthly.index');
        Route::post('/monthly', [DashboardMonthlyController::class, 'display'])->name('monthly.display');

        Route::get('/yearly', [DashboardYearlyController::class, 'index'])->name('yearly.index');
        Route::post('/yearly', [DashboardYearlyController::class, 'display'])->name('yearly.display');
        Route::get('/yearly/test', [DashboardYearlyController::class, 'test'])->name('yearly.test');

        Route::get('/other', [DashboardOtherController::class, 'index'])->name('other.index');
        Route::get('/other-grpo', [DashboardOtherController::class, 'grpo'])->name('other.grpo');
        Route::get('/other-test', [DashboardOtherController::class, 'test'])->name('other.test');

        Route::get('/summary-by-unit', [SummaryController::class, 'index'])->name('summary-by-unit');
        Route::get('/search-po', [POController::class, 'search_po'])->name('search.po');
        Route::post('/search-po', [POController::class, 'search_po_results'])->name('search.po.results');
        Route::get('/search-po/{id}', [POController::class, 'show'])->name('search.po.show');

        // Item price history routes
        Route::get('/item-price-history', [POController::class, 'item_price_history'])->name('item.price.history');
        Route::post('/item-price-history', [POController::class, 'item_price_history_results'])->name('item.price.history.results');
    });


    Route::prefix('powitheta')->name('powitheta.')->group(function () {
        Route::get('/convert-to-po', [PowithetaController::class, 'convert_to_po'])->name('convert_to_po');
        Route::get('/export_this_month', [PowithetaController::class, 'export_this_month'])->name('export_this_month');
        Route::get('/export_this_year', [PowithetaController::class, 'export_this_year'])->name('export_this_year');
        Route::get('/export_summary', [PowithetaController::class, 'export_summary'])->name('export_summary');
        Route::get('/data', [PowithetaController::class, 'data'])->name('data');
        Route::get('/data/this_year', [PowithetaController::class, 'data_this_year'])->name('data.this_year');
        Route::get('/', [PowithetaController::class, 'index'])->name('index');
        Route::get('/truncate', [PowithetaController::class, 'truncate'])->name('truncate');
        Route::get('/this_year', [PowithetaController::class, 'index_this_year'])->name('index_this_year');
        Route::get('/{id}', [PowithetaController::class, 'show'])->name('show');
        Route::post('/import_excel', [PowithetaController::class, 'import_excel'])->name('import_excel');
        Route::post('/import_oldDB', [PowithetaController::class, 'import_oldDB'])->name('import_oldDB');
        Route::get('/progress', [PowithetaController::class, 'get_progress'])->name('progress');
    });


    Route::prefix('grpo')->name('grpo.')->group(function () {
        Route::get('/export_this_month', [GrpoController::class, 'export_this_month'])->name('export_this_month');
        Route::get('/export_this_year', [GrpoController::class, 'export_this_year'])->name('export_this_year');
        Route::get('/data', [GrpoController::class, 'data'])->name('data');
        Route::get('/data/this_year', [GrpoController::class, 'data_this_year'])->name('data.this_year');
        Route::get('/', [GrpoController::class, 'index'])->name('index');
        Route::get('/truncate', [GrpoController::class, 'truncate'])->name('truncate');
        Route::get('/this_year', [GrpoController::class, 'index_this_year'])->name('index_this_year');
        Route::get('/{id}', [GrpoController::class, 'show'])->name('show');
        Route::post('/import_excel', [GrpoController::class, 'import_excel'])->name('import_excel');
    });

    Route::prefix('migi')->name('migi.')->group(function () {
        Route::get('/export_this_month', [MigiController::class, 'export_this_month'])->name('export_this_month');
        Route::get('/export_this_year', [MigiController::class, 'export_this_year'])->name('export_this_year');
        Route::get('/data', [MigiController::class, 'data'])->name('data');
        Route::get('/data/this_year', [MigiController::class, 'data_this_year'])->name('data.this_year');
        Route::get('/', [MigiController::class, 'index'])->name('index');
        Route::get('/truncate', [MigiController::class, 'truncate'])->name('truncate');
        Route::get('/this_year', [MigiController::class, 'index_this_year'])->name('index_this_year');
        Route::get('/{id}', [MigiController::class, 'show'])->name('show');
        Route::post('/import_excel', [MigiController::class, 'import_excel'])->name('import_excel');
    });

    Route::prefix('incoming')->name('incoming.')->group(function () {
        Route::get('/export_this_month', [IncomingController::class, 'export_this_month'])->name('export_this_month');
        Route::get('/export_this_year', [IncomingController::class, 'export_this_year'])->name('export_this_year');
        Route::get('/data', [IncomingController::class, 'data'])->name('data');
        Route::get('/data/this_year', [IncomingController::class, 'data_this_year'])->name('data.this_year');
        Route::get('/', [IncomingController::class, 'index'])->name('index');
        Route::get('/truncate', [IncomingController::class, 'truncate'])->name('truncate');
        Route::get('/this_year', [IncomingController::class, 'index_this_year'])->name('index_this_year');
        Route::get('/{id}', [IncomingController::class, 'show'])->name('show');
        Route::post('/import_excel', [IncomingController::class, 'import_excel'])->name('import_excel');
    });

    Route::prefix('daily-production')->name('daily-production.')->group(function () {
        Route::get('/data', [DailyProductionController::class, 'data'])->name('data');
        Route::get('/dashboard-data', [DailyProductionController::class, 'dashboardData'])->name('dashboard-data');
        Route::get('/', [DailyProductionController::class, 'index'])->name('index');
        Route::get('/create', [DailyProductionController::class, 'create'])->name('create');
        Route::post('/', [DailyProductionController::class, 'store'])->name('store');
        Route::get('/truncate', [DailyProductionController::class, 'truncate'])->name('truncate');
        Route::get('/import', [DailyProductionController::class, 'import'])->name('import');
        Route::post('/import-excel', [DailyProductionController::class, 'importExcel'])->name('import-excel');
        Route::get('/export-this-month', [DailyProductionController::class, 'exportThisMonth'])->name('export-this-month');
        Route::get('/export-this-year', [DailyProductionController::class, 'exportThisYear'])->name('export-this-year');
        Route::get('/download-template', [DailyProductionController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/{dailyProduction}', [DailyProductionController::class, 'show'])->name('show');
        Route::get('/{dailyProduction}/edit', [DailyProductionController::class, 'edit'])->name('edit');
        Route::put('/{dailyProduction}', [DailyProductionController::class, 'update'])->name('update');
        Route::delete('/{dailyProduction}', [DailyProductionController::class, 'destroy'])->name('destroy');
    });

    Route::get('budget/data', [BudgetController::class, 'data'])->name('budget.data');
    Route::resource('budget', BudgetController::class);
    Route::post('budget/copy', [BudgetController::class, 'copy_budget'])->name('budget.copy_budget');

    Route::get('budget_type/data', [BudgetTypeController::class, 'data'])->name('budget_type.data');
    Route::resource('budget_type', BudgetTypeController::class);

    Route::get('history/data', [HistoryController::class, 'data'])->name('history.data');
    Route::resource('history', HistoryController::class);
    Route::post('history/generate-monthly', [HistoryController::class, 'generate_monthly'])->name('history.generate_monthly');

    Route::get('/test', [TestController::class, 'index'])->name('index');

    Route::get('/unit-summary-table', [SummaryController::class, 'showSummaryTable']);

    Route::get('/summary/export', [SummaryController::class, 'exportExcel'])->name('summary.export');

    Route::get('/summary/export-pdf', [SummaryController::class, 'exportPdf'])->name('summary.export.pdf');
});
