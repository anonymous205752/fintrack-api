<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;

use Illuminate\Support\Facades\Route;

// AUTH
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // EXPENSES (slug based)
    Route::apiResource('expenses', ExpenseController::class)->parameters([
        'expenses' => 'slug',
    ]);

    // INCOMES (slug based)
    Route::apiResource('incomes', IncomeController::class)->parameters([
        'incomes' => 'slug',
    ]);

    // SUMMARY
    Route::get('summary', [SummaryController::class, 'index']);
    Route::get('summary/categories', [SummaryController::class, 'categorySummary']);

    // DASHBOARD
    Route::get('dashboard', [DashboardController::class, 'DashboardController']);

    // PROFILE
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile', [ProfileController::class, 'update']);

    // BUDGETS (slug-based + correct order)
    Route::get('budgets', [BudgetController::class, 'index']);
    Route::post('budgets', [BudgetController::class, 'store']);

    // Summary must be before {slug}
    Route::get('budgets/summary', [BudgetController::class, 'summary']);

    Route::get('budgets/{slug}', [BudgetController::class, 'show']);
    Route::put('budgets/{slug}', [BudgetController::class, 'update']);
    Route::delete('budgets/{slug}', [BudgetController::class, 'destroy']);

    // CATEGORIES
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
});
