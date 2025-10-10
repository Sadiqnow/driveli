<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition()
    {
        $companyTypes = ['Logistics', 'Transportation', 'Delivery', 'E-commerce', 'Food Service', 'Healthcare', 'Construction'];
        
        return [
            'company_id' => 'COMP' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => '+234' . $this->faker->numberBetween(7000000000, 9999999999),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->randomElement([
                'Lagos', 'Abuja', 'Kano', 'Rivers', 'Oyo', 'Delta', 'Kaduna', 'Ogun'
            ]),
            'country' => 'Nigeria',
            'website' => $this->faker->optional()->url(),
            'industry' => $this->faker->randomElement($companyTypes),
            'company_size' => $this->faker->randomElement(['Small (1-50)', 'Medium (51-200)', 'Large (201-1000)', 'Enterprise (1000+)']),
            'registration_number' => 'RC' . $this->faker->numerify('######'),
            'tax_identification_number' => 'TIN' . $this->faker->numerify('##########'),
            'contact_person_name' => $this->faker->name(),
            'contact_person_phone' => '+234' . $this->faker->numberBetween(7000000000, 9999999999),
            'contact_person_email' => $this->faker->email(),
            'status' => $this->faker->randomElement(['Active', 'Inactive', 'Suspended', 'Pending']),
            'verification_status' => $this->faker->randomElement(['Pending', 'Verified', 'Rejected']),
            'verified_at' => $this->faker->optional()->dateTimeThisYear(),
            'logo' => null,
            'description' => $this->faker->optional()->paragraph(),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisMonth(),
        ];
    }

    public function verified()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Active',
                'verification_status' => 'Verified',
                'verified_at' => $this->faker->dateTimeThisYear(),
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Pending',
                'verification_status' => 'Pending',
                'verified_at' => null,
            ];
        });
    }

    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Suspended',
                'verification_status' => 'Verified',
            ];
        });
    }
}