<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Total income & expenses
        $totalIncome = $user->incomes()->sum('amount') ?? 0;
        $totalExpenses = $user->expenses()->sum('amount') ?? 0;

        // Current balance
        $balance = $totalIncome - $totalExpenses;

        // Expenses grouped by category
        $expenseByCategory = $user->expenses()
            ->selectRaw('COALESCE(category, "Uncategorized") as category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        // Incomes grouped by source
        $incomeBySource = $user->incomes()
            ->selectRaw('COALESCE(source, "Unknown") as source, SUM(amount) as total')
            ->groupBy('source')
            ->get();

        // Last 5 expenses and incomes
        $recentExpenses = $user->expenses()->latest()->take(5)->get(['title', 'amount', 'date', 'category']);
        $recentIncomes = $user->incomes()->latest()->take(5)->get(['title', 'amount', 'date', 'source']);

        return response()->json([
            'balance' => $balance,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'expense_by_category' => $expenseByCategory,
            'income_by_source' => $incomeBySource,
            'recent_expenses' => $recentExpenses,
            'recent_incomes' => $recentIncomes,
            'message' => 'Dashboard summary fetched successfully',
        ]);
    }
}
