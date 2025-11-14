<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
     * Create a new budget (slug included)
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'limit'    => 'required|numeric',
            'month'    => 'required|date',
        ]);

        $slug = Str::slug($request->category . '-' . uniqid());

        $budget = Budget::create([
            'user_id'  => Auth::id(),
            'category' => $request->category,
            'limit'    => $request->limit,
            'month'    => $request->month,
            'slug'     => $slug,
        ]);

        return response()->json([
            'message' => 'Budget created successfully',
            'budget'  => $budget
        ]);
    }

    /**
     * Show a single budget using slug
     */
    public function show($slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json($budget);
    }

    /**
     * Update budget using slug
     */
    public function update(Request $request, $slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'category' => 'sometimes|string|max:255',
            'limit'    => 'sometimes|numeric',
            'month'    => 'sometimes|date',
        ]);

        // If category changes, regenerate slug
        if ($request->category) {
            $budget->slug = Str::slug($request->category . '-' . uniqid());
        }

        $budget->update($request->only(['category', 'limit', 'month']));

        return response()->json([
            'message' => 'Budget updated successfully',
            'budget'  => $budget
        ]);
    }

    /**
     * Delete budget using slug
     */
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

    /**
     * Monthly summary: budget vs spending
     */
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
                'slug'           => $budget->slug,
            ];
        });

        return response()->json($summary);
    }
}
