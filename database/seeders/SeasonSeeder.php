<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $seasons = [
            ['name' => 'Invierno', 'year' => 2025],
            ['name' => 'Primavera', 'year' => 2025],
            ['name' => 'Verano',    'year' => 2025],
            ['name' => 'Otoño',     'year' => 2025],
            ['name' => 'Invierno', 'year' => 2026],
            ['name' => 'Primavera', 'year' => 2026],
        ];

        foreach ($seasons as $season) {
            Season::create($season);
        }
    }
}