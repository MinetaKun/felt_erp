<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AdminAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop tables if they exist
        Schema::dropIfExists('order_assignments');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('artisans');
        Schema::dropIfExists('users');
        Schema::dropIfExists('departments');

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
        });

        // Create artisans table
        Schema::create('artisans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->timestamps();
        });

        // Create products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('quantity')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('product_name');
            $table->integer('total_quantity');
            $table->decimal('wages_per_unit', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        // Create order_assignments table
        Schema::create('order_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('artisan_id')->constrained('artisans')->onDelete('cascade');
            $table->integer('approved_quantity')->default(0);
            $table->integer('completed_quantity')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Create an admin user for testing
        User::create([
            'email' => 'sadmin@sadmin.com',
            'password' => Hash::make('password'),
            'name' => 'Admin User',
            'is_active' => true
        ]);
    }

    protected function tearDown(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::dropIfExists('order_assignments');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('artisans');
        Schema::dropIfExists('users');
        Schema::dropIfExists('departments');

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    /** @test */
    public function login_requires_email()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_requires_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function login_requires_valid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_requires_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com',
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }

    /** @test */
    public function admin_can_login_with_correct_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_with_inactive_account()
    {
        // Update user to be inactive
        User::where('email', 'sadmin@sadmin.com')
            ->update(['is_active' => false]);

        $response = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com',
            'password' => 'password'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Account is inactive'
            ]);
    }

    /** @test */
    public function login_should_be_throttled_after_too_many_attempts()
    {
        foreach (range(1, 6) as $_) {
            $this->postJson('/api/login', [
                'email' => 'sadmin@sadmin.com',
                'password' => 'wrong-password'
            ]);
        }

        $response = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com',
            'password' => 'password'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function admin_cannot_login_with_incorrect_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com',
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_access_dashboard_after_successful_login()
    {
        // First login
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com',
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);

        // Get the token from login response
        $token = $loginResponse->json('token');

        // Try to access dashboard with the token
        $dashboardResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/dashboard');

        $dashboardResponse->assertStatus(200);
    }

    /** @test */
    public function admin_cannot_access_dashboard_without_login()
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    /** @test */
    public function admin_sees_error_message_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sadmin@sadmin.com',
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }
}
