<?php

namespace App\Repositories\Interfaces;

use App\Http\DTO\HostReservationDTO;

interface HostReservationsInterface
{
    public function getHostReservations(HostReservationDTO $hostReservationDTO);
}
