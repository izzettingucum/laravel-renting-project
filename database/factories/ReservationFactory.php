<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReservationFactory extends Factory
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
            "office_id" => Office::factory(),
            "price" => $this->faker->numberBetween(10000,20000),
            "status" => Reservation::STATUS_ACTIVE,
            "start_date" => now()->addDay(2)->format("Y-m-d"),
            "end_date" => now()->addDay(16)->format("Y-m-d"),
            "wifi_password" => Str::random()
        ];
    }
}
