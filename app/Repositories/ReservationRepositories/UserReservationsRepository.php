<?php

namespace App\Repositories\ReservationRepositories;

use App\DTO\ReservationDTO;
use App\Models\Reservation;
use App\Repositories\Interfaces\UserReservationsInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class UserReservationsRepository implements UserReservationsInterface
{
    protected $reservationModel;

    public function __construct(Reservation $reservationModel)
    {
        $this->reservationModel = $reservationModel;
    }

    public function getUserReservations(ReservationDTO $userReservationDTO)
    {
        $query = $this->reservationModel->query()->whereUserId($userReservationDTO->userId);

        $query = $this->applyFilters($query,
            $userReservationDTO->officeId,
            $userReservationDTO->status,
            $userReservationDTO->fromDate,
            $userReservationDTO->toDate
        );

        return $query->with(["office.featuredImage"])->paginate($userReservationDTO->perPage);
    }

    protected function applyFilters($query, $officeId, $status, $fromDate, $toDate)
    {
        return $query
            ->when($officeId, function () use ($officeId) {
                return $this->reservationModel->FilterByOfficeId($officeId);
            })
            ->when($status, function () use ($status) {
                return $this->reservationModel->FilterByStatus($status);
            })
            ->when($fromDate && $toDate, function () use ($fromDate, $toDate) {
                return $this->reservationModel->FilterByDateRange($toDate, $fromDate);
            });
    }

    public function findReservationById(ReservationDTO $userReservationDTO)
    {
        $reservation = $this->reservationModel->findOrFail($userReservationDTO->id);

        return $reservation->load("office");
    }

    public function store(ReservationDTO $userReservationDTO)
    {
        $reservation = $this->reservationModel->create([
            "user_id" => $userReservationDTO->userId,
            "office_id" => $userReservationDTO->officeId,
            "price" => $userReservationDTO->price,
            "status" => $userReservationDTO->status,
            "wifi_password" => $userReservationDTO->wifiPassword,
            "start_date" => $userReservationDTO->startDate,
            "end_date" => $userReservationDTO->endDate
        ]);

        return $reservation->load("office");
    }

    public function updateStatus(ReservationDTO $userReservationDTO)
    {
        $reservation = $this->reservationModel->findOrFail($userReservationDTO->id);

        $reservation->update([
            "status" => $userReservationDTO->status
        ]);

        return $reservation->load("office");
    }
}
