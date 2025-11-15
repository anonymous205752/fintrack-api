<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Optional: filter by usage
        if ($request->has('for')) {
            $query->where('for', $request->for);
        }

        return response()->json($query->get());
    }
    public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'for' => 'nullable|in:expense,budget',
    ]);

    $category = Category::create([
        'name' => $data['name'],
        'type' => 'custom',
        'for' => $data['for'] ?? null,
    ]);

    return response()->json($category, 201);
}

}
