<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }


    public function test_can_show_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $user->projects()->attach($project->id, ['role' => '3']);

        $this->actingAs($user);

        $response = $this->get(route('projects.show', $project));

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_project()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('projects.show', $project));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Vous n\'avez pas accès à ce projet.');
    }


    public function test_can_create_project()
    {
        $response = $this->post(route('projects.store'), [
            'name' => 'Mon projet',
            'description' => 'Test',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('projects', [
            'name' => 'Mon projet',
            'description' => 'Test',
        ]);
    }

    public function test_can_update_project()
    {
        $project = Project::factory()->create();
        $project->users()->attach($this->user->id, ['role' => 3]);

        $response = $this->patch(route('projects.update', $project), [
            'name' => 'Projet modifié',
            'description' => 'Nouvelle description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Projet modifié',
            'description' => 'Nouvelle description',
        ]);
    }

    public function test_can_see_project_users()
    {
        $project = Project::factory()->create();
        $project->users()->attach($this->user->id, ['role' => 3]);

        $response = $this->get(route('projects.users', $project));
        $response->assertStatus(200);
        $response->assertViewIs('projects.users');
        $response->assertViewHas('users');
    }



}
