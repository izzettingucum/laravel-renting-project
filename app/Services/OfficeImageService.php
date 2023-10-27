<?php

namespace App\Services;

use App\Http\DTO\OfficeImageDTO;
use App\Models\Image;
use App\Models\Office;
use App\Repositories\OfficeImagesRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeImageService
{
    use AuthorizesRequests;

    protected $officeImagesRepository, $officeImageDTO;

    public function __construct(OfficeImagesRepository $officeImagesRepository, OfficeImageDTO $officeImageDTO)
    {
        $this->officeImagesRepository = $officeImagesRepository;
        $this->officeImageDTO = $officeImageDTO;
    }

    public function store(Office $office, $request)
    {
        abort_unless(auth()->user()->tokenCan("office.update"),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize("update", $office);

        $request->validated();

        $path = $request->file("image")->storePublicly("/");

        $officeImageDTO = new $this->officeImageDTO([
            "office_id" => $office->id,
            "path" => $path
        ]);

        $image = $this->officeImagesRepository->create($officeImageDTO);

        return $image;
    }

    public function delete(Office $office, Image $image)
    {
        abort_unless(auth()->user()->tokenCan("office.update"),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize("delete", $office);

        throw_if(
            $office->images()->count() == 1,
            ValidationException::withMessages(["error" => "Cannot delete the only image."])
        );

        throw_if(
            $office->featured_image_id == $image->id,
            ValidationException::withMessages(["error" => "Cannot delete the featured image."])
        );

        Storage::delete($image->path);

        $officeImageDTO = new $this->officeImageDTO([
            "id" => $image->id
        ]);

        $this->officeImagesRepository->delete($officeImageDTO);
    }
}
