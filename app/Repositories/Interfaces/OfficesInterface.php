<?php

namespace App\Repositories\Interfaces;

use App\DTO\OfficeDTO;
use App\Models\Office;
use App\Models\OfficeInfo;

interface OfficesInterface
{
    public function getOffices(OfficeDTO $officeDTO);

    public function findById(OfficeDTO $officeDTO);

    public function createOffice(OfficeDTO $officeDTO);

    public function createOfficeInfo(Office $office, OfficeDTO $officeDTO);

    public function updateOffice(Office $office, OfficeDTO $officeDTO);

    public function updateOfficeInfo(OfficeInfo $office, OfficeDTO $officeDTO);

    public function getOfficeInfo(Office $office);

    public function delete(OfficeDTO $officeDTO);

    public function attachTags(OfficeDTO $officeDTO);

    public function syncTags(Office $office, OfficeDTO $officeDTO);
}
