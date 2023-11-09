<?php

namespace App\Repositories\ReservationRepositories;

use App\DTO\reservationDTO;
use App\Models\Reservation;
use App\Repositories\Interfaces\HostReservationsInterface;

class HostReservationsRepository implements HostReservationsInterface
{
    protected $reservationModel;

    public function __construct(Reservation $reservationModel)
    {
        $this->reservationModel = $reservationModel;
    }

    public function getHostReservations(ReservationDTO $reservationDTO)
    {
        $query = $this->reservationModel->query()
            ->whereRelation("office", "user_id", "=", auth()->id());

        $query = $this->applyFilters($query,
            $reservationDTO->officeId,
            $reservationDTO->userId,
            $reservationDTO->status,
            $reservationDTO->fromDate,
            $reservationDTO->toDate);

        return $query->with(["office.featuredImage"])->paginate($reservationDTO->perPage);
    }

    protected function applyFilters($query, $officeId, $userId, $status, $fromDate, $toDate)
    {
        return $query
            ->when($officeId, function () use ($officeId) {
                return $this->reservationModel->FilterByOfficeId($officeId);
            })
            ->when($userId, function () use ($userId) {
                return $this->reservationModel->FilterByUserId($userId);
            })
            ->when($status, function () use ($status) {
                return $this->reservationModel->FilterByStatus($status);
            })
            ->when($fromDate && $toDate, function () use ($fromDate, $toDate) {
                return $this->reservationModel->FilterByDateRange($fromDate, $toDate);
            });
    }
}
