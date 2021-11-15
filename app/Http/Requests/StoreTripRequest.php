<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'origin_city' => 'required|string|min:3|max:35',
            'destination_city' => 'required|string|min:3|max:35',
            'price' => 'required|numeric',
            'take_off_time' => 'required|date_format:Y-m-d H:i:s',
            'landing_time' => 'required|date_format:Y-m-d H:i:s',
        ];
    }
}
