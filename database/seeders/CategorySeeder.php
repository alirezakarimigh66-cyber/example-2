<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['مکمل‌های بدنسازی', 'قرص‌ها و تبلت‌ها', 'ویتامین‌ها و مواد معدنی'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
