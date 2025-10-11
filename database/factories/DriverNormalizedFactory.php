<?php

namespace Database\Factories;

use App\Models\DriverNormalized;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DriverNormalizedFactory extends Factory
{
    protected $model = DriverNormalized::class;

    public function definition()
    {
        $faker = $this->faker;
        return [
            'driver_id' => 'DRV' . now()->year . Str::upper(Str::random(6)),
            'first_name' => $faker->firstName,
            'surname' => $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'phone' => $faker->phoneNumber,
            'date_of_birth' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d H:i:s'),
            'gender' => $faker->randomElement(['male', 'female']),
            'status' => 'pending',
            'verification_status' => 'pending',
            'kyc_status' => 'not_started',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
