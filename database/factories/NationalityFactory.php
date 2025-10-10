<?php

namespace Database\Factories;

use App\Models\Nationality;
use Illuminate\Database\Eloquent\Factories\Factory;

class NationalityFactory extends Factory
{
    protected $model = Nationality::class;

    public function definition()
    {
        return [
            'name' => $this->faker->country(),
            'code' => strtoupper($this->faker->unique()->lexify('??')),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function nigerian()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Nigerian',
                'code' => 'NG',
                'is_active' => true,
            ];
        });
    }
}
