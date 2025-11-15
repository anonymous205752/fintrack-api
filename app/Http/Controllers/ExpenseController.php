<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Auth::user()->expenses()->with('category')->latest()->get();
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'amount'        => 'required|numeric',
            'date'          => 'required|date',
            'category_slug' => 'nullable|string',
            'custom_category' => 'nullable|string|max:100',
        ]);

        // Resolve category from slug
        if (!empty($data['category_slug'])) {
            $category = Category::where('slug', $data['category_slug'])->firstOrFail();
            $data['category_id'] = $category->id;
        }

        // Handle custom category
        if (!empty($data['custom_category'])) {
            $customCategory = Category::firstOrCreate([
                'name' => $data['custom_category'],
                'type' => 'custom',
                'for'  => 'expense',
            ]);
            $data['category_id'] = $customCategory->id;
        }

        $data['slug'] = Str::slug($data['title'] . '-' . uniqid());

        $expense = Auth::user()->expenses()->create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'amount'      => $data['amount'],
            'date'        => $data['date'],
            'category_id' => $data['category_id'] ?? null,
            'slug'        => $data['slug'],
        ]);

        // Budget check
        if (!empty($data['category_id'])) {
            $month = Carbon::parse($data['date'])->startOfMonth();
            $budget = Budget::where('user_id', Auth::id())
                ->where('category_id', $data['category_id'])
                ->where('month', $month)
                ->first();

            if ($budget) {
                $totalSpent = Auth::user()->expenses()
                    ->where('category_id', $data['category_id'])
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
        }

        return response()->json($expense, 201);
    }

    public function show($slug)
    {
        $expense = Expense::where('slug', $slug)->where('user_id', Auth::id())
            ->with('category')
            ->firstOrFail();

        return response()->json($expense);
    }

    public function update(Request $request, $slug)
    {
        $expense = Expense::where('slug', $slug)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'description'   => 'nullable|string',
            'amount'        => 'sometimes|numeric',
            'date'          => 'sometimes|date',
            'category_slug' => 'nullable|string',
            'custom_category' => 'nullable|string|max:100',
        ]);

        if (!empty($data['category_slug'])) {
            $category = Category::where('slug', $data['category_slug'])->firstOrFail();
            $data['category_id'] = $category->id;
        }

        if (!empty($data['custom_category'])) {
            $customCategory = Category::firstOrCreate([
                'name' => $data['custom_category'],
                'type' => 'custom',
                'for'  => 'expense',
            ]);
            $data['category_id'] = $customCategory->id;
        }

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title'] . '-' . uniqid());
        }

        $expense->update($data);

        return response()->json($expense);
    }

    public function destroy($slug)
    {
        $expense = Expense::where('slug', $slug)->where('user_id', Auth::id())->firstOrFail();
        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
