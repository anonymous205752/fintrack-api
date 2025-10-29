<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to attach expenses
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found, skipping expense seeding.');
            return;
        }

        foreach ($users as $user) {
            // Create 5 sample expenses per user
            for ($i = 1; $i <= 5; $i++) {
                $title = "Expense {$i} for {$user->name}";
                $baseSlug = Str::slug($title);
                $slug = $baseSlug;
                $count = 1;

                // Ensure slug is unique
                while (Expense::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }

                Expense::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'slug' => $slug,
                    'description' => "Sample description for expense {$i}",
                    'amount' => rand(500, 50000),
                    'date' => Carbon::now()->subDays(rand(0, 30)),
                    'category' => ['Food', 'Transport', 'Shopping', 'Bills'][array_rand(['Food', 'Transport', 'Shopping', 'Bills'])],
                ]);
            }
        }

        $this->command->info('Expenses table seeded successfully!');
    }
}
