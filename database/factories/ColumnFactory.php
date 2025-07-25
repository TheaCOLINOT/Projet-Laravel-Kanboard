<?php

namespace Database\Factories;

use App\Models\Column;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColumnFactory extends Factory
{
    protected $model = Column::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'color' => $this->faker->safeColorName,
            'finished_column' => $this->faker->boolean,
            'project_id' => Project::factory(),
        ];
    }
}
