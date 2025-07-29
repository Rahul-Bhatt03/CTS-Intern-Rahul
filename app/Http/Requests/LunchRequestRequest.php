<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LunchRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'has_lunch' => 'required|boolean',
            'date' => 'sometimes|date',
        ];
    }
}

?>