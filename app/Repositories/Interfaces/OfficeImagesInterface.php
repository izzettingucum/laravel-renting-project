<?php

namespace App\Repositories\Interfaces;

use App\DTO\OfficeImageDTO;
use App\Models\Office;

interface OfficeImagesInterface
{
    public function create(Office $office, OfficeImageDTO $officeImageDTO);

    public function delete(OfficeImageDTO $officeImageDTO);
}
