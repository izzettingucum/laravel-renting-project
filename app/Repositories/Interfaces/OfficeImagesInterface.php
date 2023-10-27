<?php

namespace App\Repositories\Interfaces;

use App\Http\DTO\OfficeImageDTO;

interface OfficeImagesInterface
{
    public function create(OfficeImageDTO $officeImageDTO);

    public function delete(OfficeImageDTO $officeImageDTO);
}
