<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $people = [
            ['name' => 'Yuichi Nakamura',   'is_voice_actor' => true,  'is_staff' => false],
            ['name' => 'Yuki Kaji',         'is_voice_actor' => true,  'is_staff' => false],
            ['name' => 'Aoi Yuuki',         'is_voice_actor' => true,  'is_staff' => false],
            ['name' => 'Hiroshi Seko',      'is_voice_actor' => false, 'is_staff' => true],
            ['name' => 'Gege Akutami',      'is_voice_actor' => false, 'is_staff' => true],
            ['name' => 'Hajime Isayama',    'is_voice_actor' => false, 'is_staff' => true],
        ];

        foreach ($people as $person) {
            Person::create([
                'name'           => $person['name'],
                'slug'           => Str::slug($person['name']),
                'is_voice_actor' => $person['is_voice_actor'],
                'is_staff'       => $person['is_staff'],
                'bio'            => 'Biografía de ejemplo para ' . $person['name'] . '.',
            ]);
        }
    }
}