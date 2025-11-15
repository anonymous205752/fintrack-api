<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Auth::user()->budgets()->with('category')->get();
        return response()->json($budgets);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_slug'   => 'nullable|string',
            'custom_category' => 'nullable|string|max:100',
            'limit'           => 'required|numeric',
            'month'           => 'required|date',
        ]);

        if (!empty($data['category_slug'])) {
            $category = Category::where('slug', $data['category_slug'])->firstOrFail();
            $data['category_id'] = $category->id;
        }

        if (!empty($data['custom_category'])) {
            $customCategory = Category::firstOrCreate([
                'name' => $data['custom_category'],
                'type' => 'custom',
                'for'  => 'budget',
            ]);
            $data['category_id'] = $customCategory->id;
        }

        $category = Category::find($data['category_id']);
        $slug = Str::slug($category->name);
        $originalSlug = $slug;
        $counter = 2;

        while (Budget::where('slug', $slug)->where('user_id', Auth::id())->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $budget = Budget::create([
            'user_id'     => Auth::id(),
            'category_id' => $data['category_id'],
            'limit'       => $data['limit'],
            'month'       => $data['month'],
            'slug'        => $slug,
        ]);

        return response()->json([
            'message' => 'Budget created successfully',
            'budget'  => $budget,
        ]);
    }

    public function show($slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->with('category')
            ->firstOrFail();

        return response()->json($budget);
    }

    public function update(Request $request, $slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'category_slug'   => 'nullable|string',
            'custom_category' => 'nullable|string|max:100',
            'limit'           => 'sometimes|numeric',
            'month'           => 'sometimes|date',
        ]);

        if (!empty($data['category_slug'])) {
            $category = Category::where('slug', $data['category_slug'])->firstOrFail();
            $data['category_id'] = $category->id;
        }

        if (!empty($data['custom_category'])) {
            $customCategory = Category::firstOrCreate([
                'name' => $data['custom_category'],
                'type' => 'custom',
                'for'  => 'budget',
            ]);
            $data['category_id'] = $customCategory->id;
        }

        if (isset($data['category_id'])) {
            $category = Category::find($data['category_id']);
            $newSlug = Str::slug($category->name);
            $originalSlug = $newSlug;
            $counter = 2;

            while (Budget::where('slug', $newSlug)
                ->where('id', '!=', $budget->id)
                ->where('user_id', Auth::id())
                ->exists()
            ) {
                $newSlug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $budget->slug = $newSlug;
        }

        $budget->update($data);

        return response()->json([
            'message' => 'Budget updated successfully',
            'budget'  => $budget,
        ]);
    }

    public function destroy($slug)
    {
        $budget = Budget::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $budget->delete();

        return response()->json(['message' => 'Budget deleted successfully']);
    }

    public function summary()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();

        $budgets = $user->budgets()
            ->whereMonth('month', $currentMonth->month)
            ->whereYear('month', $currentMonth->year)
            ->with('category')
            ->get();

        $summary = $budgets->map(function ($budget) use ($user, $currentMonth) {
            $totalSpent = $user->expenses()
                ->where('category_id', $budget->category_id)
                ->whereMonth('date', $currentMonth->month)
                ->whereYear('date', $currentMonth->year)
                ->sum('amount');

            return [
                'category'     => $budget->category->name,
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
