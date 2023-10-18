<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserReservationControllerTest extends TestCase
{
    use LazilyRefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function itListsReservationsThatBelongToTheUser()
    {
        $reservationCount = 2;

        $user = User::factory()->create();

        $reservations = Reservation::factory()->for($user)->count($reservationCount)->create();

        $reservations->each(function ($reservation) {
            $image = $reservation->office->images()->create([
                "path" => $this->faker->word . ".jpg"
            ]);

            $reservation->office()->update([
                "featured_image_id" => $image->id
            ]);
        });

        $this->actingAs($user);

        $response = $this->getJson('api/reservations');

        $response->assertOk()
            ->assertJsonCount($reservationCount, "data")
            ->assertJsonPath("data.0.user_id", $user->id);

        $this->assertNotNull($response->json("data")[0]["office"]["featured_image"]);
        $this->assertNotNull($response->json("data")[0]["office"]["featured_image_id"]);
    }

    /**
     * @test
     */
    public function itListsReservationFilteredByDateRange()
    {
        $user = User::factory()->create();

        $fromDate = "2023-03-03";
        $toDate = "2023-04-04";

        $this->actingAs($user);

        // Within the date range
        $reservations = Reservation::factory()->for($user)->createMany([
            [
                "start_date" => "2023-02-25",
                "end_date" => "2023-03-05"
            ],
            [
                "start_date" => "2023-03-25",
                "end_date" => "2023-04-05"
            ],
            [
                "start_date" => "2023-03-04",
                "end_date" => "2023-03-24"
            ],
            [
                "start_date" => "2023-03-01",
                "end_date" => "2023-04-20"
            ]
        ]);

        // Within the range but belongs to a different user
        Reservation::factory()->create([
            'start_date' => '2021-03-25',
            'end_date' => '2021-03-29',
        ]);

        // Outside the date range
        Reservation::factory()->for($user)->create([
            "start_date" => "2023-02-25",
            "end_date" => "2023-03-01"
        ]);

        Reservation::factory()->for($user)->create([
            "start_date" => "2023-04-25",
            "end_date" => "2023-05-01"
        ]);

        $response = $this->getJson("api/reservations?" . http_build_query([
                "from_date" => $fromDate,
                "to_date" => $toDate
            ]));

        $response->assertOk()
            ->assertJsonCount(4, "data");

        $this->assertEquals($reservations->pluck("id")->toArray(), collect($response->json("data"))->pluck("id")->toArray());
    }

    /**
     * @test
     */
    public function itFiltersResultsByStatus()
    {
        $user = User::factory()->create();

        $reservations = Reservation::factory()->for($user)->createMany([
            [
                "status" => Reservation::STATUS_ACTIVE
            ],
            [
                "status" => Reservation::STATUS_CANCELLED
            ]
        ]);

        $this->actingAs($user);

        $response = $this->getJson("api/reservations?" . http_build_query([
                "status" => Reservation::STATUS_ACTIVE
            ]));

        $response->assertJsonCount(1, "data")
            ->assertJsonPath("data.0.id", $reservations[0]->id)
            ->assertJsonPath("data.0.status", Reservation::STATUS_ACTIVE);
    }

    /**
     * @test
     */
    public function itFiltersResultsByOffice()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $reservation = Reservation::factory()->for($office)->for($user)->create();
        $reservation2 = Reservation::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->getJson("api/reservations?" . http_build_query([
                "office_id" => $office->id
            ]));

        $response->assertJsonCount(1, "data")
            ->assertJsonPath("data.0.id", $reservation->id)
            ->assertJsonPath("data.0.office_id", $office->id);
    }

    /**
     * @test
     */
    public function itMakesReservations()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create([
            "price_per_day" => 10,
            "monthly_discount" => 10
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/reservations", [
            "office_id" => $office->id,
            "start_date" => now()->addDay(2),
            "end_date" => now()->addDay(32)
        ]);

        $response->assertCreated()
            ->assertJsonPath("data.office_id", $office->id)
            ->assertJsonPath("data.user_id", $user->id)
            ->assertJsonPath("data.status", Reservation::STATUS_ACTIVE);
    }

    /**
     * @test
     */
    public function itCannotMakeReservationOnNonExistingOffice()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson("api/reservations", [
            "office_id" => 10293841298,
            "start_date" => now()->addDay(2),
            "end_date" => now()->addDay(32)
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(["office_id" => "Invalid office_id"]);
    }

    /**
     * @test
     */
    public function itCannotMakeReservationOnOfficeThatBelongsToTheUser()
    {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->postJson("api/reservations", [
            "office_id" => $office->id,
            "start_date" => now()->addDay(2),
            "end_date" => now()->addDay(32)
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(["office_id" => "you cannot make a reservation on your own office"]);
    }

    /**
     * @test
     */
    public function itCannotMakeReservationLessThanTwoDays()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson("api/reservations", [
            "office_id" => $office->id,
            "start_date" => now()->addDay(2),
            "end_date" => now()->addDay(3)
        ])->dump();

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(["start_date" => "you cannot make a reservation for only 1 day"]);
    }

    /**
     * @test
     */
    public function itCannotMakeReservationThatIsConflicting()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create([
            "office_id" => $office->id,
            "start_date" => now()->addDay(5),
            "end_date" => now()->addDay(27)
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/reservations", [
            "office_id" => $office->id,
            "start_date" => now()->addDay(4),
            "end_date" => now()->addDay(37)
        ])->dump();

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(["start_date" => "you cannot make a reservation during this time"]);
    }

    /**
     * @test
     */
    public function itCannotMakeReservationsOnPendingOffices()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create([
            "approval_status" => Office::APPROVAL_PENDING
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/reservations", [
            "office_id" => $office->id,
            "start_date" => now()->addDay(4),
            "end_date" => now()->addDay(37)
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(["office_id" => "you cannot make a reservation on a hidden office"]);
    }
}
