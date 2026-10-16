<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'salud', 'title' => 'Salud', 'lead' => 'Cambios en atención, cobertura y prestaciones de salud.', 'sort' => 1],
            ['slug' => 'educacion', 'title' => 'Educación', 'lead' => 'Cambios en el acceso a la educación y sus apoyos.', 'sort' => 2],
            ['slug' => 'trabajo-y-pensiones', 'title' => 'Trabajo y pensiones', 'lead' => 'Cambios en empleo, salarios y pensiones.', 'sort' => 3],
            ['slug' => 'vivienda', 'title' => 'Vivienda', 'lead' => 'Cambios en vivienda, arriendo y barrio.', 'sort' => 4],
            ['slug' => 'proteccion-social', 'title' => 'Protección social', 'lead' => 'Cambios en cuidados, subvenciones y programas sociales.', 'sort' => 5],
            ['slug' => 'estado-y-servicios', 'title' => 'Estado y servicios', 'lead' => 'Cambios en servicios e instituciones del Estado.', 'sort' => 6],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
