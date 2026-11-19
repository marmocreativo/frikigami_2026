<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $estrenos = [
            ['name' => 'Otoño', 'year' => 2026],
            ['name' => 'Invierno', 'year' => 2026],
            ['name' => 'Primavera', 'year' => 2027],
        ];

        foreach ($estrenos as $estreno) {
            Season::firstOrCreate($estreno);
        }
    }
}
