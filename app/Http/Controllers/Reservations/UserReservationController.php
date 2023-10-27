<?php

namespace App\Http\Controllers\Reservations;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserReservations\CreateRequest;
use App\Http\Requests\UserReservations\IndexRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\UserReservationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserReservationController extends Controller
{
    protected $userReservationService;

    public function __construct(UserReservationService $userReservationService)
    {
        $this->userReservationService = $userReservationService;
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
        $reservation = $this->userReservationService->create($request);

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
