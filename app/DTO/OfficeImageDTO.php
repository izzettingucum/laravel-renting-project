<?php

namespace App\DTO;

use App\Traits\StaticCreateSelf;

class OfficeImageDTO
{
    use StaticCreateSelf;

    public $id, $office_id, $path;
}
