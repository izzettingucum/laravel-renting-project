<?php

namespace App\Http\Requests\HostReservations;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReservationIndexRequest extends FormRequest
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
            "status" => Rule::in([Reservation::STATUS_ACTIVE, Reservation::STATUS_CANCELLED]),
            "office_id" => ["integer"],
            "user_id" => ["integer"],
            "from_date" => ["date", "required_with:to_date"],
            "to_date" => ["date", "required_with:from_date", "after:from_date"]
        ];
    }

    public function validated(): array
    {
        $validator = Validator::make($this->all(), $this->rules());

        return $validator->validated();
    }
}
