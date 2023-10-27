<?php

namespace App\Repositories;

use App\Http\DTO\OfficeImageDTO;
use App\Models\Image;
use App\Models\Office;
use App\Repositories\Interfaces\OfficeImagesInterface;

class OfficeImagesRepository implements OfficeImagesInterface
{
    protected $imageModel, $officeModel;

    public function __construct(Image $imageModel, Office $officeModel)
    {
        $this->imageModel = $imageModel;
        $this->officeModel = $officeModel;
    }

    public function create(OfficeImageDTO $officeImageDTO)
    {
        $office = $this->officeModel->findOrFail($officeImageDTO->office_id);
        return $office->images()->create([
            "path" => $officeImageDTO->path
        ]);
    }

    public function delete(OfficeImageDTO $officeImageDTO)
    {
        return $this->imageModel->whereId($officeImageDTO->id)->delete();
    }
}
