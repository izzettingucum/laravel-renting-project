<?php

namespace App\Http\Controllers\Reservations;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserReservations\CreateRequest;
use App\Http\Requests\UserReservations\IndexRequest;
use App\Http\Resources\ReservationResource;
use App\Services\OfficeServices\OfficeService;
use App\Services\ReservationServices\UserReservationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserReservationController extends Controller
{
    protected $userReservationService, $officeService;

    public function __construct(UserReservationService $userReservationService, OfficeService $officeService)
    {
        $this->userReservationService = $userReservationService;
        $this->officeService = $officeService;
    }

    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $reservations = $this->userReservationService->index($request);

        return ReservationResource::collection(
            $reservations
        );
    }

    public function create(CreateRequest $request): ReservationResource
    {
        $office = $this->officeService->findOfficeById($request->office_id);
        $reservation = $this->userReservationService->makeReservation($office, $request);

        return ReservationResource::make(
            $reservation
        );
    }

    public function cancel($id): ReservationResource
    {
        $reservation = $this->userReservationService->cancel($id);

        return ReservationResource::make(
            $reservation
        );
    }
}
