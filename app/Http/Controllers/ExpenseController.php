<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Budget;

class ExpenseController extends Controller
{
    // List all expenses
    public function index()
    {
        $expenses = Auth::user()->expenses()->latest()->get(); // no with('category')
        return response()->json($expenses);
    }

    // Create new expense
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric',
            'date'        => 'required|date',
            'category'    => 'required|string|max:255', // category as string
        ]);

        $data['slug'] = Str::slug($data['title'] . '-' . uniqid());

        $expense = Auth::user()->expenses()->create($data);

        // Budget check
        $month = Carbon::parse($data['date'])->startOfMonth();
        $budget = Budget::where('user_id', Auth::id())
            ->where('category', $data['category'])
            ->where('month', $month)
            ->first();

        if ($budget) {
            $totalSpent = Auth::user()->expenses()
                ->where('category', $data['category'])
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->sum('amount');

            if ($totalSpent > $budget->limit) {
                return response()->json([
                    'expense'     => $expense,
                    'message'     => '⚠️ You have exceeded your budget for this category this month!',
                    'totalSpent'  => $totalSpent,
                    'budgetLimit' => $budget->limit,
                ], 201);
            }
        }

        return response()->json($expense, 201);
    }

    // Show expense by slug
    public function show($slug)
    {
        $expense = Expense::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json($expense);
    }

    // Update expense
    public function update(Request $request, $slug)
    {
        $expense = Expense::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'sometimes|numeric',
            'date'        => 'sometimes|date',
            'category'    => 'sometimes|string|max:255',
        ]);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title'] . '-' . uniqid());
        }

        $expense->update($data);

        return response()->json($expense);
    }

    // Delete expense
    public function destroy($slug)
    {
        $expense = Expense::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
