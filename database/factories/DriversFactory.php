<?php

namespace Database\Factories;

use App\Models\Drivers;
use App\Models\Nationality;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DriversFactory extends Factory
{
    protected $model = Drivers::class;

    public function definition()
    {
        return [
            'driver_id' => 'DRV' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'nickname' => $this->faker->optional()->firstName(),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'surname' => $this->faker->lastName(),
            'phone' => '+234' . $this->faker->numberBetween(7000000000, 9999999999),
            // Make phone_2 optional and less likely to be generated during tests to avoid DB length issues
            'phone_2' => $this->faker->optional(0.3)->regexify('\+234[789][0-9]{9}'),
            'email' => $this->faker->unique()->safeEmail(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'religion' => $this->faker->randomElement(['Christianity', 'Islam', 'Traditional', 'Other']),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'height_meters' => $this->faker->randomFloat(2, 1.50, 2.10),
            'disability_status' => $this->faker->randomElement(['None', 'Physical', 'Visual', 'Hearing', 'Other']),
            'nationality_id' => function () {
                // Prefer an existing nationality to avoid duplicate unique code inserts during tests
                $existing = \App\Models\Nationality::inRandomOrder()->first();
                if ($existing) {
                    return $existing->id;
                }
                return Nationality::factory()->create()->id;
            },
            'profile_picture' => 'profiles/' . $this->faker->uuid() . '.jpg',
            'nin_number' => $this->faker->numerify('###########'),
            'license_number' => strtoupper($this->faker->bothify('??###??####')),
            'license_class' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'verification_status' => $this->faker->randomElement(['pending', 'reviewing', 'verified', 'rejected']),
            'is_active' => $this->faker->boolean(80),
            'last_active_at' => $this->faker->optional()->dateTimeThisMonth(),
            'registered_at' => $this->faker->dateTimeThisYear(),
            'verified_at' => $this->faker->optional()->dateTimeThisMonth(),
            'verification_notes' => $this->faker->optional()->sentence(),
            'rejected_at' => null,
            'rejection_reason' => null,
            'ocr_verification_status' => $this->faker->randomElement(['pending', 'processing', 'verified', 'failed']),
            'ocr_verification_notes' => $this->faker->optional()->sentence(),
            'email_verified_at' => $this->faker->dateTimeThisYear(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function verified()
    {
        return $this->state(function (array $attributes) {
            return [
                'verification_status' => 'verified',
                'verified_at' => $this->faker->dateTimeThisMonth(),
                'ocr_verification_status' => 'verified',
                'status' => 'active',
                'is_active' => true,
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'verification_status' => 'pending',
                'verified_at' => null,
                'ocr_verification_status' => 'pending',
                'status' => 'inactive',
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'verification_status' => 'rejected',
                'verified_at' => null,
                'rejected_at' => $this->faker->dateTimeThisMonth(),
                'rejection_reason' => $this->faker->sentence(),
                'status' => 'inactive',
                'is_active' => false,
            ];
        });
    }
}