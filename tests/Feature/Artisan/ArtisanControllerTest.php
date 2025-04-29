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
use Illuminate\Http\UploadedFile;

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

        // Create test artisan with all required fields
        Artisan::create([
            'name' => 'Test Artisan',
            'email' => 'test@example.com',
            'phone_number' => '1234567890',
            'basic_salary' => 50000,
            'pan_number' => 'TEST123456',
            'bank_account_number' => '12345678901234',
            'department_id' => $this->department->id,
            'status' => 'active',
            'skills' => json_encode(['skill1', 'skill2'])
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
                        'email',
                        'phone_number',
                        'basic_salary',
                        'pan_number',
                        'department' => [
                            'id',
                            'name'
                        ],
                        'status',
                        'skills',
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
            'email' => 'new@example.com',
            'phone_number' => '9876543210',
            'basic_salary' => 50000,
            'pan_number' => 'NEW123456',
            'bank_account_number' => '98765432109876',
            'department_id' => $this->department->id,
            'status' => 'active',
            'skills' => json_encode(['skill1', 'skill2'])
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/artisans', $artisanData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New Artisan',
                'email' => 'new@example.com',
                'phone_number' => '9876543210',
                'basic_salary' => '50000.00',
                'pan_number' => 'NEW123456',
                'bank_account_number' => '98765432109876',
                'department_id' => $this->department->id,
                'status' => 'active',
                'skills' => ['skill1', 'skill2']
            ]);

        $this->assertDatabaseHas('artisans', [
            'name' => 'New Artisan',
            'email' => 'new@example.com',
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
            ->assertJson(['message' => 'Artisan deleted']);

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
            'name' => 'Test Department ' . uniqid()
        ]);

        // Simulate user login
        $this->actingAs($user);

        // Try to access the add artisan form
        $response = $this->get('/artisans/create');

        // Assert successful response
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_update_an_artisan_with_all_fields()
    {
        $artisan = Artisan::first();
        $updateData = [
            'name' => 'Updated Artisan',
            'email' => 'updated@example.com',
            'phone_number' => '9876543210',
            'basic_salary' => 60000,
            'pan_number' => 'UPDATED1234',
            'bank_account_number' => '98765432109876',
            'department_id' => $this->department->id,
            'status' => 'inactive',
            'skills' => json_encode(['updated skill 1', 'updated skill 2'])
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/artisans/{$artisan->id}", array_merge($updateData, ['_method' => 'PATCH']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Artisan',
                    'email' => 'updated@example.com',
                    'phone_number' => '9876543210',
                    'basic_salary' => 60000,
                    'pan_number' => 'UPDATED1234',
                    'bank_account_number' => '98765432109876',
                    'status' => 'inactive'
                ],
                'message' => 'Artisan updated successfully'
            ]);

        $this->assertDatabaseHas('artisans', [
            'id' => $artisan->id,
            'name' => 'Updated Artisan',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function it_can_update_an_artisan_with_partial_fields()
    {
        $artisan = Artisan::first();
        $updateData = [
            'name' => 'Partially Updated',
            'status' => 'inactive'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/artisans/{$artisan->id}", array_merge($updateData, ['_method' => 'PATCH']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Partially Updated',
                    'status' => 'inactive'
                ],
                'message' => 'Artisan updated successfully'
            ]);

        $this->assertDatabaseHas('artisans', [
            'id' => $artisan->id,
            'name' => 'Partially Updated',
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function it_validates_unique_fields_during_update()
    {
        // Create another artisan first
        $anotherArtisan = Artisan::create([
            'name' => 'Another Artisan',
            'email' => 'another@example.com',
            'phone_number' => '1111111111',
            'basic_salary' => 50000,
            'pan_number' => 'ANOTHER1234',
            'bank_account_number' => '11111111111111',
            'department_id' => $this->department->id,
            'status' => 'active',
            'skills' => json_encode(['skill1'])
        ]);

        $artisan = Artisan::first();
        $updateData = [
            'email' => 'another@example.com', // Try to use email from another artisan
            'pan_number' => 'ANOTHER1234', // Try to use PAN from another artisan
            'bank_account_number' => '11111111111111' // Try to use bank account from another artisan
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/artisans/{$artisan->id}", array_merge($updateData, ['_method' => 'PATCH']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'pan_number', 'bank_account_number']);
    }

    /** @test */
    public function it_can_update_artisan_with_file_uploads()
    {
        $artisan = Artisan::first();

        // Create test files
        $profilePhoto = UploadedFile::fake()->image('profile.jpg');
        $citizenshipPhoto = UploadedFile::fake()->image('citizenship.jpg');

        $updateData = [
            'name' => 'Updated With Photos',
            'profile_photo' => $profilePhoto,
            'citizenship_photo' => $citizenshipPhoto
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/artisans/{$artisan->id}", array_merge($updateData, ['_method' => 'PATCH']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated With Photos'
                ],
                'message' => 'Artisan updated successfully'
            ]);

        // Assert that the files were stored
        $this->assertNotNull($response->json('data.profile_photo_url'));
        $this->assertNotNull($response->json('data.citizenship_photo_url'));
    }
}
