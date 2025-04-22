<?php

namespace Database\Factories;

use App\Models\Artisan;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtisanFactory extends Factory
{
    protected $model = Artisan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $this->faker->phoneNumber,
            'basic_salary' => $this->faker->numberBetween(20000, 50000),
            'pan_number' => $this->faker->unique()->numerify('PAN######'),
            'department_id' => Department::inRandomOrder()->first()->id ?? Department::factory()->create()->id,
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
