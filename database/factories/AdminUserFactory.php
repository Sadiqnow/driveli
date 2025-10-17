<?php

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminUserFactory extends Factory
{
    protected $model = AdminUser::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'phone' => '+234' . $this->faker->numberBetween(7000000000, 9999999999),
            'role' => $this->faker->randomElement(['admin', 'manager', 'operator']),
            'status' => $this->faker->randomElement(['Active', 'Inactive', 'Suspended']),
            'permissions' => $this->faker->randomElements([
                'view_drivers', 'manage_drivers', 'verify_drivers',
                'view_companies', 'manage_companies',
                'view_requests', 'manage_requests',
                'view_reports', 'generate_reports',
                'manage_users', 'system_settings'
            ], $this->faker->numberBetween(2, 6)),
            // Use dateTimeBetween for portability across Faker versions
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'last_login_ip' => $this->faker->optional()->ipv4(),
            'avatar' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'Super Admin',
                'status' => 'Active',
                'permissions' => ['manage_drivers'], // Super admin has all permissions
            ];
        });
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Active',
                'last_login_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Inactive',
                'last_login_at' => $this->faker->optional()->dateTimeBetween('-1 years', '-30 days'),
            ];
        });
    }
}