<?php

namespace App\Http\Requests\Offices;

use App\Models\Office;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CrudRequest extends FormRequest
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
        $this->id == null ? $id = null : $id = $this->id;

        return [
            'title' => [Rule::when($id, 'sometimes'), 'required', 'string'],
            'description' => [Rule::when($id, 'sometimes'), 'required', 'string'],
            'lat' => [Rule::when($id, 'sometimes'), 'required', 'numeric'],
            'lng' => [Rule::when($id, 'sometimes'), 'required', 'numeric'],
            'address_line1' => [Rule::when($id, 'sometimes'), 'required', 'string'],
            'price_per_day' => [Rule::when($id, 'sometimes'), 'required', 'integer', 'min:100'],
            "monthly_discount" => ["integer", "min:0", "max:100"],
            "hidden" => ["boolean"],
            'featured_image_id' => [
                Rule::exists('images', 'id')
                    ->where('resource_type', 'office')
                    ->where('resource_id', $id),
            ],
            "tags" => ["array"],
            "tags.*" => ["integer", Rule::exists("tags", "id")]
        ];
    }

    public function validated()
    {
        $validator = Validator::make($this->all(), $this->rules());

        return $validator->validated();
    }
}
