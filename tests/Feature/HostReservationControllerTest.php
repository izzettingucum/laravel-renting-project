<?php

namespace Tests\Feature;

use App\Models\Office;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HostReservationControllerTest extends TestCase
{
    Use LazilyRefreshDatabase, WithFaker;

     /**
     * @test
     */
    public function itListsReservationsBelongsToUserWhoHasOffice()
    {
        $reservationCount = 1;

        $host = User::factory()->create();

        $user = User::factory()->create();

        $office = Office::factory()->for($host)->create();

        [$reservation] = Reservation::factory($reservationCount)->for($office)->for($user)->create();

        $this->actingAs($host);

        $response = $this->getJson('api/host/reservations')->dump();

        $response->assertOk()
            ->assertJsonPath("data.0.id", $reservation->id)
            ->assertJsonPath("data.0.office_id", $office->id)
            ->assertJsonPath("data.0.office.id", $office->id)
            ->assertJsonCount($reservationCount, "data");
    }

     /**
     * @test
     */
     public function itListReservationFilteredByOfficeId()
     {
         $host = User::factory()->create();
         $user = User::factory()->create();

         $office1 = Office::factory()->for($host)->create();
         $office2 = Office::factory()->for($host)->create();

         $reservation1 = Reservation::factory()->for($user)->for($office1)->create();
         $reservation2 = Reservation::factory()->for($user)->for($office2)->create();

         $this->actingAs($host);

         $response = $this->getJson("api/host/reservations?" . http_build_query([
             "office_id" => $office1->id
         ]))->dump();

         $response->assertJsonCount(1, "data")
             ->assertJsonPath("data.0.id", $reservation1->id);
     }

      /**
      * @test
      */
      public function itListReservationFilteredByUserId()
      {
          $reservationCount = 2;

          $host = User::factory()->create();

          $user1 = User::factory()->create();
          $user2 = User::factory()->create();

          $office = Office::factory()->for($host)->create();

          $this->actingAs($host);

          [$reservation1] = Reservation::factory($reservationCount)->for($user1)->for($office)->create();
          $reservation2 = Reservation::factory()->for($user2)->for($office)->create();

          $response = $this->getJson("api/host/reservations?" . http_build_query([
              "user_id" => $user1->id
          ]))->dump();

          $response->assertJsonCount($reservationCount, "data")
              ->assertJsonPath("data.0.id", $reservation1->id);
      }
}
