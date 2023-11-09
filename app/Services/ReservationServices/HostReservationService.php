<?php

namespace App\Services\ReservationServices;

use App\DTO\ReservationDTO;
use App\Repositories\ReservationRepositories\HostReservationsRepository;

class HostReservationService
{

    protected $hostReservationsRepository, $reservationDTO;

    public function __construct(HostReservationsRepository $hostReservationsRepository, ReservationDTO $reservationDTO)
    {
        $this->hostReservationsRepository = $hostReservationsRepository;
        $this->reservationDTO = $reservationDTO;
    }

    public function index($request)
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
}
