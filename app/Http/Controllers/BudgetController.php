<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BudgetController extends Controller
{
    // List all budgets for authenticated user
    public function index()
    {
        $budgets = Auth::user()->budgets()->get(); // category is string, no relation
        return response()->json($budgets);
    }

    // Create a new budget
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string|max:255',
            'limit'    => 'required|numeric',
            'month'    => 'required|date',
        ]);

        // Slug generation handled automatically in the model

        $budget = Budget::create([
            'user_id'  => Auth::id(),
            'category' => $data['category'],
            'limit'    => $data['limit'],
            'month'    => $data['month'],
        ]);

        return response()->json([
            'message' => 'Budget created successfully',
            'budget'  => $budget,
        ]);
    }

    // Show a single budget by slug
    public function show($slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json($budget);
    }

    // Update a budget
    public function update(Request $request, $slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'category' => 'sometimes|string|max:255',
            'limit'    => 'sometimes|numeric',
            'month'    => 'sometimes|date',
        ]);

        $budget->update($data);

        return response()->json([
            'message' => 'Budget updated successfully',
            'budget'  => $budget,
        ]);
    }

    // Delete a budget
    public function destroy($slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted successfully'
        ]);
    }

    // Monthly summary: budget vs spent
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
                'category'     => $budget->category,
                'budget_limit' => $budget->limit,
                'total_spent'  => $totalSpent,
                'remaining'    => $budget->limit - $totalSpent,
                'overspent'    => $totalSpent > $budget->limit,
                'slug'         => $budget->slug,
            ];
        });

        return response()->json($summary);
    }
}
