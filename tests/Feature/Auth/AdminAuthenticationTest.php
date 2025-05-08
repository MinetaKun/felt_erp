<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_redirected_to_dashboard_after_login()
    {
        // Arrange: Create a test admin user
        $user = User::factory()->create([
            'email' => 'ssadmin@sadmin.com',
            'password' => bcrypt('password'),
        ]);

        // Act: Attempt login
        $response = $this->post('/login', [
            'email' => 'ssadmin@sadmin.com',
            'password' => 'password',
        ]);

        // Assert: User is redirected to dashboard
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function error_shown_on_wrong_password()
    {
        // Arrange: Create a test admin user
        $user = User::factory()->create([
            'email' => 'ssadmin@sadmin.com',
            'password' => bcrypt('password'),
        ]);

        // Act: Try to login with wrong password
        $response = $this->from('/login')->post('/login', [
            'email' => 'ssadmin@sadmin.com',
            'password' => 'wrongpassword',
        ]);

        // Assert: Redirects back to login with error
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
