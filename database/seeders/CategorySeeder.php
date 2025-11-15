<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Tuition & Academic Charges', 'type' => 'predefined', 'for' => 'expense'],
            ['name' => 'Project Materials', 'type' => 'predefined', 'for' => 'expense'],
            ['name' => 'Books & Supplies', 'type' => 'predefined', 'for' => 'expense'],
            ['name' => 'Accommodation', 'type' => 'predefined', 'for' => 'expense'],
            ['name' => 'Feeding', 'type' => 'predefined', 'for' => 'expense'],
            ['name' => 'Transport', 'type' => 'predefined', 'for' => 'expense'],
            ['name' => 'Data & Airtime', 'type' => 'predefined', 'for' => 'expense'],
            ['name' => 'Other (Custom Category)', 'type' => 'predefined', 'for' => 'expense'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate($cat);
        }
    }
}
