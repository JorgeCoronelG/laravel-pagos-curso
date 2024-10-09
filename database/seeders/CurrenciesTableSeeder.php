<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        Currency::query()->insert([
            ['iso' => 'mxn', 'created_at' => $now],
            ['iso' => 'usd', 'created_at' => $now],
            ['iso' => 'eur', 'created_at' => $now],
            ['iso' => 'jpy', 'created_at' => $now],
        ]);
    }
}
