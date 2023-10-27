<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use App\Services\OfficeImageService;

class OfficeImageController extends Controller
{
    protected $officeImageService;

    public function __construct(OfficeImageService $officeImageService)
    {
        $this->officeImageService = $officeImageService;
    }

    public function store(Office $office, OfficeImageRequest $request)
    {
        $image = $this->officeImageService->store($office, $request);

        return ImageResource::make(
            $image
        );
    }

    public function delete(Office $office, Image $image)
    {
        $this->officeImageService->delete($office, $image);
    }
}
