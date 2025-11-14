<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\ProfileController; // ✅ Add this line
use App\Http\Controllers\BudgetController;

use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // ✅ use slug instead of id for both
    Route::apiResource('expenses', ExpenseController::class)->parameters([
        'expenses' => 'slug',
    ]);

    Route::apiResource('incomes', IncomeController::class)->parameters([
        'incomes' => 'slug',
    ]);

    // ✅ Summary & Dashboard routes
    Route::get('summary', [SummaryController::class, 'index']);
    Route::get('summary/categories', [SummaryController::class, 'categorySummary']);

    // ✅ Dashboard route
    Route::get('dashboard', [DashboardController::class, 'DashboardController']);

    // ✅ Profile routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    
    //Budget routes
    Route::get('budgets', [BudgetController::class, 'index']);
Route::post('budgets', [BudgetController::class, 'store']);
Route::get('budgets/{slug}', [BudgetController::class, 'show']);
Route::put('budgets/{slug}', [BudgetController::class, 'update']);
Route::delete('budgets/{slug}', [BudgetController::class, 'destroy']);
Route::get('budgets/summary', [BudgetController::class, 'summary']);

});
