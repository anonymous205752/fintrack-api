<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('budgets', function (Blueprint $table) {
        $table->string('slug')->nullable()->unique()->after('category');
    });

    // Backfill slug for existing rows
    $budgets = \App\Models\Budget::all();
    foreach ($budgets as $budget) {
        $slug = \Illuminate\Support\Str::slug($budget->category);

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 2;

        while (\App\Models\Budget::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $budget->slug = $slug;
        $budget->save();
    }

    // Make slug required
    Schema::table('budgets', function (Blueprint $table) {
        $table->string('slug')->nullable(false)->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            //
        });
    }
};
