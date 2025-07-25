<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Column $column;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create();
        $this->project->users()->attach($this->user->id, ['role' => '3']);

        $this->column = Column::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_store_task()
    {
        $response = $this->post(route('tasks.store', [$this->project, $this->column]), [
            'title' => 'Test Task',
            'description' => 'Description test',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
            'column_id' => $this->column->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_store_task_from_list()
    {
        $response = $this->post(route('tasks.storeFromList', $this->project), [
            'title' => 'Liste Task',
            'description' => 'Tâche depuis liste',
            'due_date' => now()->addDays(3)->toDateString(),
            'column_id' => $this->column->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Liste Task',
            'project_id' => $this->project->id,
            'column_id' => $this->column->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_delete_task()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'column_id' => $this->column->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->delete(route('tasks.delete', $task));

        $response->assertRedirect();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_can_update_task()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'column_id' => $this->column->id,
            'user_id' => $this->user->id,
            'title' => 'Ancien titre',
        ]);

        $response = $this->patch(route('tasks.update', $task), [
            'title' => 'Titre mis à jour',
            'description' => 'Description mise à jour',
            'due_date' => now()->addDays(10)->toDateString(),
            'column_id' => $this->column->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Titre mis à jour',
        ]);
    }


    public function test_can_reorder_tasks()
    {
        $task1 = Task::factory()->create([
            'project_id' => $this->project->id,
            'column_id' => $this->column->id,
            'user_id' => $this->user->id,
            'order' => 1,
        ]);
        $task2 = Task::factory()->create([
            'project_id' => $this->project->id,
            'column_id' => $this->column->id,
            'user_id' => $this->user->id,
            'order' => 2,
        ]);

        $response = $this->post(route('tasks.reorder'), [
            'column_id' => $this->column->id,
            'tasks' => [
                ['id' => $task2->id, 'order' => 1],
                ['id' => $task1->id, 'order' => 2],
            ],
        ]);


        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task2->id,
            'order' => 1,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $task1->id,
            'order' => 2,
        ]);
    }
}
