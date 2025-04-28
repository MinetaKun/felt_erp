<?php

namespace Tests\Feature\Artisan;

use App\Models\Artisan;
use App\Models\User;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ArtisanProductivityControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $department;
    protected $artisan;

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
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        // Create test artisan
        $this->artisan = Artisan::create([
            'name' => 'Test Artisan',
            'department_id' => $this->department->id,
            'status' => 'active'
        ]);

        // Create test order
        $order = Order::create([
            'order_number' => 'TEST-001',
            'product_name' => 'Test Product',
            'quantity' => 100,
            'status' => 'in_progress'
        ]);

        // Create order assignment
        OrderAssignment::create([
            'order_id' => $order->id,
            'artisan_id' => $this->artisan->id,
            'assigned_quantity' => 50,
            'completed_quantity' => 30,
            'status' => 'in_progress',
            'assigned_at' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()
        ]);
    }

    /** @test */
    public function it_can_get_artisan_productivity_summary()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/artisans/{$this->artisan->id}/productivity");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'artisan_id',
                    'total_orders',
                    'total_assigned',
                    'total_completed',
                    'completion_rate',
                    'average_completion_time'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_artisan_productivity_by_date_range()
    {
        $startDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->getJson("/api/artisans/{$this->artisan->id}/productivity?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'artisan_id',
                    'period_start',
                    'period_end',
                    'total_orders',
                    'total_assigned',
                    'total_completed',
                    'completion_rate',
                    'average_completion_time'
                ]
            ]);
    }

    /** @test */
    public function it_can_export_artisan_productivity_report()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/artisans/{$this->artisan->id}/productivity/export");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function it_returns_404_when_artisan_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/artisans/999/productivity');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_validates_date_range_parameters()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/artisans/{$this->artisan->id}/productivity?start_date=invalid&end_date=invalid");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }
}
