<?php

namespace App\Http\Requests;

use App\Models\Office;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfficeRequest extends FormRequest
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
        $this->office == null ? $office = new Office() : $office = $this->office;

        return [
            'title' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
            'description' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
            'lat' => [Rule::when($office->exists, 'sometimes'), 'required', 'numeric'],
            'lng' => [Rule::when($office->exists, 'sometimes'), 'required', 'numeric'],
            'address_line1' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
            'price_per_day' => [Rule::when($office->exists, 'sometimes'), 'required', 'integer', 'min:100'],
            "monthly_discount" => ["integer", "min:0", "max:100"],
            "hidden" => ["boolean"],
            'featured_image_id' => [
                Rule::exists('images', 'id')
                    ->where('resource_type', 'office')
                    ->where('resource_id', $office->id),
            ],
            "tags" => ["array"],
            "tags.*" => ["integer", Rule::exists("tags", "id")]
        ];
    }
}
