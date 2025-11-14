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
        $table->string('slug')->nullable()->after('id'); // temporarily nullable
    });

    // Backfill slug for existing rows
    $budgets = \App\Models\Budget::all();
    foreach ($budgets as $budget) {
        $budget->slug = \Illuminate\Support\Str::slug($budget->category . '-' . uniqid());
        $budget->save();
    }

    // Make slug NOT NULL
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
