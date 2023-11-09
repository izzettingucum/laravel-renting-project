<?php

namespace App\Repositories\Interfaces;

use App\DTO\OfficeDTO;

interface OfficesInterface
{
    public function getOffices(OfficeDTO $officeDTO);

    public function findById(OfficeDTO $officeDTO);

    public function createOffice(OfficeDTO $officeDTO);

    public function update(OfficeDTO $officeDTO);

    public function delete(OfficeDTO $officeDTO);

    public function attachTags(OfficeDTO $officeDTO);

    public function syncTags(OfficeDTO $officeDTO);
}
