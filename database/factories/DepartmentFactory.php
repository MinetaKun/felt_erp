<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition()
    {
        $departmentNames = [
            'Wet Felting',
            'Needling',
            'Shoes',
            'Knitting',
            'Sewing',
            'Embroidery',
            'Quality Control',
            'Packaging',
            'Dyeing',
            'Finishing',
        ];
        return [
            'name' => $this->faker->unique()->randomElement($departmentNames),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
