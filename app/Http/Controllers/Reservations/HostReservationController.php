<?php

namespace App\Http\Controllers\Reservations;

use App\Http\Controllers\Controller;
use App\Http\Requests\HostReservations\ReservationIndexRequest;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationServices\HostReservationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HostReservationController extends Controller
{
    protected $hostReservationService;

    public function __construct(HostReservationService $hostReservationService)
    {
        $this->hostReservationService = $hostReservationService;
    }

    public function index(ReservationIndexRequest $request): AnonymousResourceCollection
    {
        $reservations = $this->hostReservationService->getHostReservations($request);

        return ReservationResource::collection(
            $reservations
        );
    }
}
