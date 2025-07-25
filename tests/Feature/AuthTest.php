<?php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'lastname' => 'Doe',
            'firstname' => 'John',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_cannot_register_with_invalid_data()
    {
        $response = $this->from('/register')->post('/register', [
            'lastname' => '',
            'firstname' => '',
            'email' => 'falseEmail',
            'password' => '123',
            'password_confirmation' => '321',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['lastname', 'firstname', 'email', 'password']);
        $this->assertGuest();
    }
}
