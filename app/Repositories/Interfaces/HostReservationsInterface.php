<?php

namespace App\Repositories\Interfaces;

use App\DTO\ReservationDTO;

interface HostReservationsInterface
{
    public function getHostReservations(ReservationDTO $reservationDTO);
}
