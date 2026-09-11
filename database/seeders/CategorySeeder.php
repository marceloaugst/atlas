<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Programação',
            'Arquitetura de Software',
            'Banco de Dados',
            'Ficção',
            'Romance',
            'Tecnologia',
            'Negócios',
            'Finanças',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'active' => true]
            );
        }
    }
}
