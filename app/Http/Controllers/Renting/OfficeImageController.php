<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeImageController extends Controller
{
    public function store(Office $office, OfficeImageRequest $request)
    {
        abort_unless(auth()->user()->tokenCan("office.update"),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize("update", $office);

        $request->validated();

        $path = $request->file("image")->storePublicly("/", ["disk" => "public"]);

        $image = $office->images()->create([
            "path" => $path
        ]);

        return ImageResource::make(
            $image
        );
    }

    public function delete(Office $office, Image $image)
    {
        abort_unless(auth()->user()->tokenCan("office.update"),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize("delete", $office);

        throw_if(
            $image->resource_type != "office" || $image->resource_id != $office->id,
            ValidationException::withMessages(["error" => "Cannot delete the this image."])
        );

        throw_if(
            $office->images()->count() == 1,
            ValidationException::withMessages(["error" => "Cannot delete the only image."])
        );

        throw_if(
            $office->featured_image_id == $image->id,
            ValidationException::withMessages(["error" => "Cannot delete the featured image."])
        );

        Storage::disk("public")->delete($image->path);

        $image->delete();
    }
}
