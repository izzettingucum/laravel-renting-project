<?php

namespace App\Repositories\Interfaces;

use App\Http\DTO\UserReservationDTO;

interface UserReservationsInterface
{
    public function getUserReservations(UserReservationDTO $userReservationDTO);

    public function findById(UserReservationDTO $userReservationDTO);

    public function store(UserReservationDTO $userReservationDTO);

    public function updateStatus(UserReservationDTO $userReservationDTO);
}
