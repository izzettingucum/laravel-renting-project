<?php

namespace App\Http\Requests\Offices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'title' => 'required|string',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address_line1' => 'required|string',
            'price_per_day' => 'required|integer|min:100',
            'monthly_discount' => 'integer|min:0|max:100',
            'hidden' => 'boolean',
            'featured_image_id' => [
                Rule::exists('images', 'id')
                    ->where('resource_type', 'office')
                    ->where('resource_id', null),
            ],
            'tags' => 'array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }

    public function validated()
    {
        $validator = Validator::make($this->all(), $this->rules());

        return $validator->validated();
    }
}
