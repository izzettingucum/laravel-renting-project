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
    Use LazilyRefreshDatabase, WithFaker;

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

        $response = $this->getJson('api/reservations')->dump();

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
         ]))->dump();

         $response->assertOk()
             ->assertJsonCount(3, "data");

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
          ]))->dump();

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
           ]))->dump();

           $response->assertJsonCount(1, "data")
               ->assertJsonPath("data.0.id", $reservation->id)
               ->assertJsonPath("data.0.office_id", $office->id);
       }
}
