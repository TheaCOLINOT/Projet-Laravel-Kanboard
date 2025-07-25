<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = $this->faker->sentence(2);

        return [
            'name' => $name,
            'description' => $this->faker->paragraph,
            'slug' => Str::slug($name),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
