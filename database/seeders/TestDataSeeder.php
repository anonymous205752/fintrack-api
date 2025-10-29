<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Expense;
use App\Models\Income;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password', // will be hashed automatically
        ]);

        // Create some expenses
        $expenses = [
            ['title' => 'Groceries', 'description' => 'Weekly groceries', 'amount' => 5000, 'date' => now(), 'category' => 'Food'],
            ['title' => 'Transport', 'description' => 'Bus and taxi fare', 'amount' => 2000, 'date' => now(), 'category' => 'Transport'],
            ['title' => 'Entertainment', 'description' => 'Movies and events', 'amount' => 3000, 'date' => now(), 'category' => 'Leisure'],
        ];

        foreach ($expenses as $expense) {
            $user->expenses()->create($expense);
        }

        // Create some incomes
        $incomes = [
            ['title' => 'Salary', 'description' => 'Monthly salary', 'amount' => 100000, 'date' => now(), 'category' => 'Job'],
            ['title' => 'Freelance', 'description' => 'Project payment', 'amount' => 20000, 'date' => now(), 'category' => 'Freelance'],
            ['title' => 'Gift', 'description' => 'Birthday gift', 'amount' => 5000, 'date' => now(), 'category' => 'Other'],
        ];

        foreach ($incomes as $income) {
            $user->incomes()->create($income);
        }
    }
}
