<?php

namespace App\Http\Requests\Offices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OfficeListRequest extends FormRequest
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
            "user_id" => ["sometimes", "numeric"],
            "visitor_id" => ["sometimes", "numeric"],
            "lat" => ["sometimes", "required_with:lng", "regex:/^[-]?[0-9]*\.?[0-9]+$/"],
            "lng" => ["sometimes", "required_with:lat", "regex:/^[-]?[0-9]*\.?[0-9]+$/"]
        ];
    }

    public function validated()
    {
        $validator = Validator::make($this->all(), $this->rules());

        return $validator->validated();
    }
}
