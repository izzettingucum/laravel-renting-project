<?php

namespace App\Services\ReservationServices;

use App\DTO\ReservationDTO;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\Reservations\NewHostReservation;
use App\Repositories\ReservationRepositories\HostReservationsRepository;
use Illuminate\Support\Facades\Notification;

class HostReservationService
{

    protected $hostReservationsRepository, $reservationDTO;

    public function __construct(HostReservationsRepository $hostReservationsRepository, ReservationDTO $reservationDTO)
    {
        $this->hostReservationsRepository = $hostReservationsRepository;
        $this->reservationDTO = $reservationDTO;
    }

    public function getHostReservations($request)
    {
        $reservationDTO = $this->reservationDTO->create([
            "officeId" => $request->office_id,
            "userId" => $request->user_id,
            "status" => $request->status,
            "fromDate" => $request->fromDate,
            "toDate" => $request->to_date,
            "perPage" => 20
        ]);

        $reservations = $this->hostReservationsRepository->getHostReservations($reservationDTO);

        return $reservations;
    }

    public function sendNewHostReservationNotification(User $user, Reservation $reservation)
    {
        Notification::send($user, new NewHostReservation($reservation));
    }
}
