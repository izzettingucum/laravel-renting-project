<?php

namespace App\Http\Controllers\Reservations;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserReservations\CreateRequest;
use App\Http\Requests\UserReservations\IndexRequest;
use App\Http\Resources\ReservationResource;
use App\Services\OfficeServices\OfficeService;
use App\Services\ReservationServices\HostReservationService;
use App\Services\ReservationServices\UserReservationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserReservationController extends Controller
{
    protected $userReservationService, $hostReservationService, $officeService;

    public function __construct(UserReservationService $userReservationService, HostReservationService $hostReservationService, OfficeService $officeService)
    {
        $this->userReservationService = $userReservationService;
        $this->hostReservationService = $hostReservationService;
        $this->officeService = $officeService;
    }

    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $reservations = $this->userReservationService->getUserReservations($request);

        return ReservationResource::collection(
            $reservations
        );
    }

    public function create(CreateRequest $request): ReservationResource
    {
        $office = $this->officeService->findOfficeById($request->office_id);
        $reservation = $this->userReservationService->makeReservation($office, $request);
        $this->userReservationService->sendNewUserReservationNotification(auth()->user(), $reservation);
        $this->hostReservationService->sendNewHostReservationNotification($office->user, $reservation);

        return ReservationResource::make(
            $reservation
        );
    }

    public function cancel($id): ReservationResource
    {
        $reservation = $this->userReservationService->cancel($id);
        $this->userReservationService->sendNewUserReservationNotification(auth()->user(), $reservation);
        $this->hostReservationService->sendNewHostReservationNotification($reservation->office->user, $reservation);

        return ReservationResource::make(
            $reservation
        );
    }
}
