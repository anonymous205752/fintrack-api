<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get optional date filters
        $from = $request->query('from');
        $to   = $request->query('to');

        // Build base queries
        $incomeQuery = Income::where('user_id', $user->id);
        $expenseQuery = Expense::where('user_id', $user->id);

        // Apply date filters if provided
        if ($from && $to) {
            $incomeQuery->whereBetween('date', [$from, $to]);
            $expenseQuery->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            $incomeQuery->where('date', '>=', $from);
            $expenseQuery->where('date', '>=', $from);
        } elseif ($to) {
            $incomeQuery->where('date', '<=', $to);
            $expenseQuery->where('date', '<=', $to);
        }

        // Calculate totals
        $totalIncome = $incomeQuery->sum('amount');
        $totalExpense = $expenseQuery->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Optional: monthly breakdown
        if ($request->boolean('monthly')) {
            $incomeByMonth = $incomeQuery
                ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
                ->groupBy('month')
                ->get();

            $expenseByMonth = $expenseQuery
                ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
                ->groupBy('month')
                ->get();

            return response()->json([
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'balance' => $balance,
                    'period' => [
                        'from' => $from,
                        'to' => $to,
                    ],
                ],
                'monthly' => [
                    'income' => $incomeByMonth,
                    'expense' => $expenseByMonth,
                ],
            ]);
        }

        // Default: overall summary
        return response()->json([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'period' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }
    public function categorySummary(Request $request)
{
    $user = Auth::user();

    $from = $request->query('from');
    $to = $request->query('to');

    // Build base queries
    $incomeQuery = \App\Models\Income::where('user_id', $user->id);
    $expenseQuery = \App\Models\Expense::where('user_id', $user->id);

    // Apply optional date filters
    if ($from && $to) {
        $incomeQuery->whereBetween('date', [$from, $to]);
        $expenseQuery->whereBetween('date', [$from, $to]);
    } elseif ($from) {
        $incomeQuery->where('date', '>=', $from);
        $expenseQuery->where('date', '>=', $from);
    } elseif ($to) {
        $incomeQuery->where('date', '<=', $to);
        $expenseQuery->where('date', '<=', $to);
    }

    // Group by category
    $incomeBySource = $incomeQuery
        ->selectRaw('source, SUM(amount) as total')
        ->groupBy('source')
        ->get();

    $expenseByCategory = $expenseQuery
        ->selectRaw('category, SUM(amount) as total')
        ->groupBy('category')
        ->get();

    return response()->json([
        'income_by_source' => $incomeBySource,
        'expense_by_category' => $expenseByCategory,
        'period' => [
            'from' => $from,
            'to' => $to,
        ],
    ]);
}

}
