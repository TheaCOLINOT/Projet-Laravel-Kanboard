<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Column;
use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'order' => $this->faker->numberBetween(1, 10),
            'project_id' => Project::factory(),
            'priority_id' => Priority::factory(),
            'column_id' => Column::factory(),
            'user_id' => User::factory(),
        ];
    }
}
