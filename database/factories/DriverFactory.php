<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'driver_id' => 'DRV-' . strtoupper($this->faker->unique()->randomNumber(6)),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional(0.3)->firstName(),
            'surname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+234' . $this->faker->numerify('##########'),
            'phone_2' => $this->faker->optional(0.5)->numerify('+234##########'),
            'password' => Hash::make('password'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'onboarding', 'pending_review']),
            'verification_status' => $this->faker->randomElement(['pending', 'verified', 'rejected']),
            'is_active' => $this->faker->boolean(80),
            'is_available' => $this->faker->boolean(70),
            'kyc_status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'rejected']),
            'kyc_step' => $this->faker->numberBetween(1, 6),
            'kyc_retry_count' => $this->faker->numberBetween(0, 3),
            'profile_completion_percentage' => $this->faker->numberBetween(0, 100),
            'registration_source' => $this->faker->randomElement(['web', 'mobile', 'admin', 'api']),
            'registration_ip' => $this->faker->ipv4(),
            'email_verified_at' => $this->faker->optional(0.7)->dateTime(),
            'phone_verified_at' => $this->faker->optional(0.6)->dateTime(),
            'verified_at' => $this->faker->optional(0.5)->dateTime(),
            'verified_by' => null,
            'verification_notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the driver is active.
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'is_active' => true,
                'is_available' => true,
                'verification_status' => 'verified',
                'kyc_status' => 'completed',
                'profile_completion_percentage' => 100,
            ];
        });
    }

    /**
     * Indicate that the driver is in onboarding.
     */
    public function onboarding()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'onboarding',
                'is_active' => false,
                'is_available' => false,
                'verification_status' => 'pending',
                'kyc_status' => 'pending',
                'profile_completion_percentage' => $this->faker->numberBetween(0, 50),
            ];
        });
    }

    /**
     * Indicate that the driver is pending review.
     */
    public function pendingReview()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending_review',
                'is_active' => false,
                'is_available' => false,
                'verification_status' => 'pending',
                'kyc_status' => 'submitted',
                'profile_completion_percentage' => 100,
            ];
        });
    }

    /**
     * Indicate that the driver is verified.
     */
    public function verified()
    {
        return $this->state(function (array $attributes) {
            return [
                'verification_status' => 'verified',
                'verified_at' => now(),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ];
        });
    }
}
