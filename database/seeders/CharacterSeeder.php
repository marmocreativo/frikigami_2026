<?php

namespace Database\Seeders;

use App\Models\Character;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CharacterSeeder extends Seeder
{
    public function run(): void
    {
        $characters = [
            'Yuji Itadori',
            'Megumi Fushiguro',
            'Nobara Kugisaki',
            'Satoru Gojo',
            'Eren Yeager',
            'Mikasa Ackerman',
            'Armin Arlert',
        ];

        foreach ($characters as $name) {
            Character::create([
                'name'        => $name,
                'slug'        => Str::slug($name),
                'description' => 'Descripción de ejemplo para ' . $name . '.',
            ]);
        }
    }
}