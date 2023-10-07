<?php

namespace App\Http\Controllers\Renting;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeImageController extends Controller
{
    public function store(Office $office, OfficeImageRequest $request)
    {
        $request->validated();

        $path = $request->file("image")->storePublicly("/", ["disk" => "public"]);

        $image = $office->images()->create([
            "path" => $path
        ]);

        return ImageResource::make(
            $image
        );
    }
}
