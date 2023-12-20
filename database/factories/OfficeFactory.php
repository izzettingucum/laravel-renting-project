<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Office;
use App\Models\OfficeInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id" => User::factory(),
            "lat" => $this->faker->latitude,
            "lng" => $this->faker->longitude,
            "approval_status" => Office::APPROVAL_APPROVED,
            "hidden" => false
        ];
    }

    public function withOfficeInfo($attributes = [])
    {
        return $this->afterCreating(function (Office $office) use ($attributes) {
            OfficeInfo::create([
                "office_id" => $office->id,
                "title" => $attributes["title"] ?? $this->faker->title,
                "description" => $attributes["description"] ?? $this->faker->text,
                "address_line1" => $attributes["address_line1"] ?? $this->faker->address,
                "address_line2" => $attributes["address_line2"] ?? $this->faker->address,
                "price_per_day" => $attributes["price_per_day"] ?? $this->faker->numberBetween(100, 10000),
                "monthly_discount" => $attributes["monthly_discount"] ?? $this->faker->numberBetween(0, 100)
            ]);
        });
    }
}
