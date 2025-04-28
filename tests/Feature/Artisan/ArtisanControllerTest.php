<?php

namespace Tests\Feature\Artisan;

use App\Models\Artisan;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Auth;

class ArtisanControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithoutMiddleware;

    protected $admin;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a department
        $this->department = Department::create([
            'name' => 'Test Department'
        ]);

        // Create an admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true
        ]);

        // Create test artisan
        Artisan::create([
            'name' => 'Test Artisan',
            'department_id' => $this->department->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_can_list_all_artisans()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/artisans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'department_id',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_new_artisan()
    {
        $artisanData = [
            'name' => 'New Artisan',
            'department_id' => $this->department->id,
            'status' => 'active'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/artisans', $artisanData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'New Artisan',
                    'department_id' => $this->department->id,
                    'status' => 'active'
                ]
            ]);

        $this->assertDatabaseHas('artisans', [
            'name' => 'New Artisan',
            'department_id' => $this->department->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_artisan()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/artisans', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'department_id']);
    }

    /** @test */
    public function it_can_update_an_artisan()
    {
        $artisan = Artisan::first();
        $updateData = [
            'name' => 'Updated Artisan',
            'status' => 'inactive'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/artisans/{$artisan->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Artisan',
                    'status' => 'inactive'
                ]
            ]);

        $this->assertDatabaseHas('artisans', [
            'id' => $artisan->id,
            'name' => 'Updated Artisan',
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function it_can_delete_an_artisan()
    {
        $artisan = Artisan::first();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/artisans/{$artisan->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Artisan deleted successfully']);

        $this->assertDatabaseMissing('artisans', [
            'id' => $artisan->id
        ]);
    }

    /** @test */
    public function it_returns_404_when_artisan_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/artisans/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function user_can_access_add_artisan_form()
    {
        // Create a user
        $user = User::factory()->create([
            'is_active' => true
        ]);

        // Create a department (needed for the form)
        $department = Department::create([
            'name' => 'Test Department'
        ]);

        // Simulate user login
        $this->actingAs($user);

        // Try to access the add artisan form
        $response = $this->get('/artisans/create');

        // Assert successful response
        $response->assertStatus(200);
    }
}
