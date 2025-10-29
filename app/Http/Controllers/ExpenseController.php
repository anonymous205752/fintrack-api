<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Auth::user()->expenses()->latest()->get();
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric',
            'date'        => 'required|date',
            'category'    => 'nullable|string|max:100',
        ]);

        $data['slug'] = Str::slug($data['title'] . '-' . uniqid());

        $expense = Auth::user()->expenses()->create($data);

        return response()->json($expense, 201);
    }

    public function show($slug)
    {
        $expense = Expense::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json($expense);
    }

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
            'category'    => 'nullable|string|max:100',
        ]);

        // If title changes, update slug
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title'] . '-' . uniqid());
        }

        $expense->update($data);
        return response()->json($expense);
    }

    public function destroy($slug)
    {
        $expense = Expense::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
