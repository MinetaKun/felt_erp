<?php

namespace Tests\Feature\Artisan;

use App\Models\Artisan;
use App\Models\User;
use App\Models\Department;
use App\Models\ArtisanAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ArtisanAttendanceControllerTest extends TestCase
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
    }

    /** @test */
    public function it_can_mark_artisan_attendance_as_present()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/artisans/{$this->artisan->id}/attendance", [
                'status' => 'present',
                'date' => Carbon::now()->format('Y-m-d')
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'artisan_id' => $this->artisan->id,
                    'status' => 'present'
                ]
            ]);

        $this->assertDatabaseHas('artisan_attendances', [
            'artisan_id' => $this->artisan->id,
            'status' => 'present'
        ]);
    }

    /** @test */
    public function it_can_mark_artisan_attendance_as_absent()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/artisans/{$this->artisan->id}/attendance", [
                'status' => 'absent',
                'date' => Carbon::now()->format('Y-m-d')
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'artisan_id' => $this->artisan->id,
                    'status' => 'absent'
                ]
            ]);

        $this->assertDatabaseHas('artisan_attendances', [
            'artisan_id' => $this->artisan->id,
            'status' => 'absent'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_marking_attendance()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/artisans/{$this->artisan->id}/attendance", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'date']);
    }

    /** @test */
    public function it_returns_404_when_artisan_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/artisans/999/attendance', [
                'status' => 'present',
                'date' => Carbon::now()->format('Y-m-d')
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_get_artisan_attendance_for_date_range()
    {
        // Create some attendance records
        ArtisanAttendance::create([
            'artisan_id' => $this->artisan->id,
            'status' => 'present',
            'date' => Carbon::now()->subDays(2)
        ]);

        ArtisanAttendance::create([
            'artisan_id' => $this->artisan->id,
            'status' => 'absent',
            'date' => Carbon::now()->subDays(1)
        ]);

        $startDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->getJson("/api/artisans/{$this->artisan->id}/attendance?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'artisan_id',
                        'status',
                        'date'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_update_attendance_record()
    {
        $attendance = ArtisanAttendance::create([
            'artisan_id' => $this->artisan->id,
            'status' => 'present',
            'date' => Carbon::now()->format('Y-m-d')
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/artisans/{$this->artisan->id}/attendance/{$attendance->id}", [
                'status' => 'absent'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'absent'
                ]
            ]);

        $this->assertDatabaseHas('artisan_attendances', [
            'id' => $attendance->id,
            'status' => 'absent'
        ]);
    }
}
