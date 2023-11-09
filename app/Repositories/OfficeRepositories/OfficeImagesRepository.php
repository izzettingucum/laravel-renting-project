<?php

namespace App\Repositories\OfficeRepositories;

use App\DTO\OfficeImageDTO;
use App\Models\Image;
use App\Models\Office;
use App\Repositories\Interfaces\OfficeImagesInterface;

class OfficeImagesRepository implements OfficeImagesInterface
{
    protected $imageModel;

    public function __construct(Image $imageModel)
    {
        $this->imageModel = $imageModel;
    }

    public function create(Office $office, OfficeImageDTO $officeImageDTO)
    {
        return $office->images()->create([
            "path" => $officeImageDTO->path
        ]);
    }

    public function delete(OfficeImageDTO $officeImageDTO)
    {
        return $this->imageModel->whereId($officeImageDTO->id)->delete();
    }
}
