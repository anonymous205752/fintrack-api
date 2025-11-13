<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Expense;


class BudgetController extends Controller
{
    /**
     * List all budgets for the authenticated user
     */
    public function index()
    {
        $budgets = Auth::user()->budgets()->get();
        return response()->json($budgets);
    }

    /**
     * Create a new budget or update if exists for same category & month
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'limit'    => 'required|numeric',
            'month'    => 'required|date',
        ]);

        $budget = Budget::updateOrCreate(
            [
                'user_id'  => Auth::id(),
                'category' => $request->category,
                'month'    => $request->month,
            ],
            ['limit' => $request->limit]
        );

        return response()->json([
            'message' => 'Budget set successfully',
            'budget'  => $budget
        ]);
    }

    /**
     * Update an existing budget
     */
    public function update(Request $request, Budget $budget)
    {
        // Ensure the budget belongs to the authenticated user
        $this->authorize('update', $budget);

        $request->validate([
            'category' => 'sometimes|string|max:255',
            'limit'    => 'sometimes|numeric',
            'month'    => 'sometimes|date',
        ]);

        $budget->update($request->only(['category', 'limit', 'month']));

        return response()->json([
            'message' => 'Budget updated successfully',
            'budget'  => $budget
        ]);
    }

    /**
     * Delete a budget
     */
    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted successfully'
        ]);
    }
     public function summary()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();

        $budgets = $user->budgets()
            ->whereMonth('month', $currentMonth->month)
            ->whereYear('month', $currentMonth->year)
            ->get();

        $summary = $budgets->map(function ($budget) use ($user, $currentMonth) {
            $totalSpent = $user->expenses()
                ->where('category', $budget->category)
                ->whereMonth('date', $currentMonth->month)
                ->whereYear('date', $currentMonth->year)
                ->sum('amount');

            return [
                'category'       => $budget->category,
                'budget_limit'   => $budget->limit,
                'total_spent'    => $totalSpent,
                'remaining'      => $budget->limit - $totalSpent,
                'overspent'      => $totalSpent > $budget->limit,
            ];
        });

        return response()->json($summary);
    }
}
