<?php

namespace App\Services;

use App\Http\DTO\HostReservationDTO;
use App\Repositories\HostReservationsRepository;
use Illuminate\Http\Response;

class HostReservationService
{

    protected $hostReservationsRepository, $hostReservationDTO;

    public function __construct(HostReservationsRepository $hostReservationsRepository, HostReservationDTO $hostReservationDTO)
    {
        $this->hostReservationsRepository = $hostReservationsRepository;
        $this->hostReservationDTO = $hostReservationDTO;
    }

    public function index($request)
    {
        abort_unless(auth()->user()->tokenCan("reservations.show"),
            Response::HTTP_FORBIDDEN
        );

        $hostReservationDTO = new $this->hostReservationDTO([
            "officeId" => $request->office_id,
            "userId" => $request->user_id,
            "status" => $request->status,
            "fromDate" => $request->fromDate,
            "toDate" => $request->to_date,
            "perPage" => 20
        ]);

        $reservations = $this->hostReservationsRepository->getHostReservations($hostReservationDTO);

        return $reservations;
    }
}
