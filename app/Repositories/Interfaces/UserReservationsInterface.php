<?php

namespace App\Repositories\Interfaces;

use App\DTO\ReservationDTO;

interface UserReservationsInterface
{
    public function getUserReservations(ReservationDTO $userReservationDTO);

    public function findById(ReservationDTO $userReservationDTO);

    public function store(ReservationDTO $userReservationDTO);

    public function updateStatus(ReservationDTO $userReservationDTO);
}
