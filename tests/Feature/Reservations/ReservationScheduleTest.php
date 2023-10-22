<?php

namespace Tests\Feature\Reservations;

use App\Models\Reservation;
use App\Notifications\Reservations\HostReservationStarting;
use App\Notifications\Reservations\UserReservationStarting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservationScheduleTest extends TestCase
{
    Use LazilyRefreshDatabase;


    /**
     * @test
     */
    public function itSendsNotificationToHostAndUserOnReservationDay()
    {
        Notification::fake();

        $reservation = Reservation::factory()->create([
            "status" => Reservation::STATUS_ACTIVE,
            "start_date" => now()->toDateString()
        ]);

        Artisan::call("send:due-reservations-notifications");

        Notification::assertSentTo($reservation->user, UserReservationStarting::class);
        Notification::assertSentTo($reservation->office->user, HostReservationStarting::class);
    }
}
