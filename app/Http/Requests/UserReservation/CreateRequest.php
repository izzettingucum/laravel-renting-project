<?php

namespace App\Http\Requests\UserReservation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

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
            "office_id" => ["required", "integer"],
            "start_date" => ["required", "date:Y-m-d", "after:" . now()->addDay()->toDateString()],
            "end_date" => ["required", "date:Y-m-d", "after:start_date"]
        ];
    }


    public function validated()
    {
        $validator = Validator::make($this->all(), $this->rules());

        return $validator->validated();
    }
}
