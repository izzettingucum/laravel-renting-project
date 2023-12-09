<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use App\Services\OfficeServices\OfficeImageService;
use Illuminate\Http\Response;

class OfficeImageController extends Controller
{
    protected $officeImageService;

    public function __construct(OfficeImageService $officeImageService)
    {
        $this->officeImageService = $officeImageService;
    }

    public function store(Office $office, OfficeImageRequest $request)
    {
        $this->authorize("update", $office);

        $image = $this->officeImageService->store($office, $request);

        return ImageResource::make(
            $image
        );
    }

    public function delete(Office $office, Image $image)
    {
        $this->authorize("delete", $office);

        $this->officeImageService->delete($office, $image);

        return response()->json([
            "message" => "Image deleted successfully"
        ], Response::HTTP_OK);
    }
}
