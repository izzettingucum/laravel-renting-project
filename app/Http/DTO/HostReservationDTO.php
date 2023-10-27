<?php

namespace App\Http\DTO;

class HostReservationDTO
{
    public $officeId, $userId, $status, $fromDate, $toDate, $perPage;

    public function __construct(array $data)
    {
        $properties = get_object_vars($this);

        foreach ($properties as $property => $value) {
            if (array_key_exists($property, $data)) {
                $this->{$property} = $data[$property] ?? null;
            }
        }
    }
}
