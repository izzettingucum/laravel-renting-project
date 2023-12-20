<?php

namespace App\Services\OfficeServices;

use App\DTO\OfficeImageDTO;
use App\Models\Image;
use App\Models\Office;
use App\Repositories\Interfaces\OfficeImagesInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeImageService
{
    use AuthorizesRequests;

    protected $officeImagesRepository, $officeImageDTO;

    public function __construct(OfficeImagesInterface $officeImagesRepository, OfficeImageDTO $officeImageDTO)
    {
        $this->officeImagesRepository = $officeImagesRepository;
        $this->officeImageDTO = $officeImageDTO;
    }

    public function store(Office $office, $request)
    {
        $path = $request->file("image")->storePublicly("/");

        $officeImageDTO = $this->officeImageDTO->create([
            "office_id" => $office->id,
            "path" => $path
        ]);

        $image = $this->officeImagesRepository->create($office, $officeImageDTO);

        return $image;
    }

    public function delete(Office $office, Image $image)
    {
        throw_if(
            $office->images()->count() == 1,
            ValidationException::withMessages(["error" => "Cannot delete the only image."])
        );

        throw_if(
            $office->featured_image_id == $image->id,
            ValidationException::withMessages(["error" => "Cannot delete the featured image."])
        );

        Storage::delete($image->path);

        $officeImageDTO = $this->officeImageDTO->create([
            "id" => $image->id
        ]);

        $this->officeImagesRepository->delete($officeImageDTO);
    }
}
