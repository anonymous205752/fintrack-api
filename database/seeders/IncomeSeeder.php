<?php

namespace Database\Seeders;

use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class IncomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure you have users to attach incomes to
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found, skipping income seeding.');
            return;
        }

        foreach ($users as $user) {
            // Create 5 sample incomes for each user
            for ($i = 1; $i <= 5; $i++) {
                $title = "Income {$i} for {$user->name}";
                $baseSlug = Str::slug($title);
                $slug = $baseSlug;
                $count = 1;

                // Ensure slug is unique
                while (Income::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }

                Income::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'slug' => $slug,
                    'description' => "Sample description for income {$i}",
                    'amount' => rand(1000, 100000),
                    'date' => Carbon::now()->subDays(rand(0, 30)),
                    'source' => ['Salary', 'Freelance', 'Business'][array_rand(['Salary', 'Freelance', 'Business'])],
                ]);
            }
        }

        $this->command->info('Income table seeded successfully!');
    }
}
