<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class OfficeImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "image" => ["required", "file", "max:5000", "mimes:jpg,png"]
        ];
    }

    public function validated()
    {
        $validator = Validator::make($this->all(), $this->rules());

        return $validator->validated();
    }
}
