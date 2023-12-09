<?php

namespace Reservations;

use App\Models\Office;
use App\Models\Permission;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HostReservationControllerTest extends TestCase
{
    Use WithFaker, LazilyRefreshDatabase;

     /**
     * @test
     */
    public function itListsReservationsBelongsToUserWhoHasOffice()
    {
        $reservationCount = 1;

        $host = User::factory()->withRole(ROLE::ROLE_USER)->create();

        $user = User::factory()->create();

        $office = Office::factory()->for($host)->create();

        [$reservation] = Reservation::factory($reservationCount)->for($office)->for($user)->create();

        $this->actingAs($host);

        $response = $this->getJson(route("host.reservations.list"));

        $response->assertOk()
            ->assertJsonPath("data.0.id", $reservation->id)
            ->assertJsonPath("data.0.office_id", $office->id)
            ->assertJsonPath("data.0.office.id", $office->id)
            ->assertJsonCount($reservationCount, "data");
    }

     /**
     * @test
     */
     public function itListsReservationsFilteredByOfficeId()
     {
         $host = User::factory()->withRole(ROLE::ROLE_USER)->create();
         $user = User::factory()->create();

         $office1 = Office::factory()->for($host)->create();
         $office2 = Office::factory()->for($host)->create();

         $reservation1 = Reservation::factory()->for($user)->for($office1)->create();
         $reservation2 = Reservation::factory()->for($user)->for($office2)->create();

         $this->actingAs($host);

         $response = $this->getJson(route("host.reservations.list") . "?" . http_build_query([
             "office_id" => $office1->id
         ]));

         $response->assertJsonCount(1, "data")
             ->assertJsonPath("data.0.id", $reservation1->id);
     }

      /**
      * @test
      */
      public function itListsReservationsFilteredByUserId()
      {
          $reservationCount = 2;

          $host = User::factory()->withRole(ROLE::ROLE_USER)->create();

          $user1 = User::factory()->create();
          $user2 = User::factory()->create();

          $office = Office::factory()->for($host)->create();

          $this->actingAs($host);

          [$reservation1] = Reservation::factory($reservationCount)->for($user1)->for($office)->create();
          $reservation2 = Reservation::factory()->for($user2)->for($office)->create();

          $response = $this->getJson(route("host.reservations.list") . "?" . http_build_query([
              "user_id" => $user1->id
          ]));

          $response->assertJsonCount($reservationCount, "data")
              ->assertJsonPath("data.0.id", $reservation1->id);
      }

       /**
       * @test
       */
       public function itListsReservationsFilteredByStatus()
       {
           $host = User::factory()->withRole(ROLE::ROLE_USER)->create();

           $user = User::factory()->create();

           $office = Office::factory()->for($host)->create();

           $reservationActive = Reservation::factory()->for($office)->for($user)->create([
               "status" => Reservation::STATUS_ACTIVE
           ]);

           $reservationCancelled = Reservation::factory()->for($office)->for($user)->create([
               "status" => Reservation::STATUS_CANCELLED
           ]);

           $this->actingAs($host);

           $response = $this->getJson(route("host.reservations.list") . "?" . http_build_query([
               "status" => Reservation::STATUS_ACTIVE
           ]));

           $response->assertOk()
               ->assertJsonCount(1, "data")
               ->assertJsonPath("data.0.id", $reservationActive->id)
               ->assertJsonPath("data.0.status", Reservation::STATUS_ACTIVE);
       }

     /**
     * @test
     */
     public function itListsReservationFilteredByDateRange()
     {
         $user = User::factory()->create();
         $host = User::factory()->withRole(ROLE::ROLE_USER)->create();

         $office = Office::factory()->for($host)->create();

         $fromDate = "2023-03-03";
         $toDate = "2023-04-04";

         $this->actingAs($host);

         // Within the date range
         $reservations = Reservation::factory()->for($office)->for($user)->createMany([
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

         $response = $this->getJson(route("host.reservations.list") . "?" . http_build_query([
                 "from_date" => $fromDate,
                 "to_date" => $toDate
             ]));

         $response->assertOk()
             ->assertJsonCount(3, "data");

         $this->assertEquals($reservations->pluck("id")->toArray(), collect($response->json("data"))->pluck("id")->toArray());
     }
}
