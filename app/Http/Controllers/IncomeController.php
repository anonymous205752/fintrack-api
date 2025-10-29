<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index()
    {
        $incomes = Auth::user()->incomes()->latest()->get();
        return response()->json($incomes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:255',
        ]);

        $income = Auth::user()->incomes()->create($request->all());

        return response()->json([
            'message' => 'Income added successfully!',
            'data' => $income
        ], 201);
    }

    public function show(string $slug)
    {
        $income = Auth::user()->incomes()->where('slug', $slug)->firstOrFail();
        return response()->json($income);
    }

    public function update(Request $request, string $slug)
    {
        $income = Auth::user()->incomes()->where('slug', $slug)->firstOrFail();

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'date' => 'sometimes|date',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:255',
        ]);

        $income->update($request->all());

        return response()->json([
            'message' => 'Income updated successfully!',
            'data' => $income
        ]);
    }

    public function destroy(string $slug)
    {
        $income = Auth::user()->incomes()->where('slug', $slug)->firstOrFail();
        $income->delete();

        return response()->json(['message' => 'Income deleted successfully!']);
    }
}
