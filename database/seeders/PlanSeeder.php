<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::query()->create([
            'slug' => 'monthly',
            'price' => 10000,
            'duration_in_days' => 30
        ]);

        Plan::query()->create([
            'slug' => 'yearly',
            'price' => 100000,
            'duration_in_days' => 365
        ]);
    }
}
