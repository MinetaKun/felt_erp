<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Department;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artisan>
 */
class ArtisanFactory extends Factory
{
    public function definition()
    {
        $department = Department::inRandomOrder()->first() ?? Department::factory()->create();

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'profile_photo' => $this->faker->imageUrl(200, 200, 'people'),
            'citizenship_photo' => $this->faker->imageUrl(200, 200, 'documents'),
            'pan_number' => $this->faker->unique()->regexify('[A-Z]{5}[0-9]{4}[A-Z]{1}'),
            'department_id' => $department->id,
            'basic_salary' => $this->faker->randomFloat(2, 5000, 20000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
