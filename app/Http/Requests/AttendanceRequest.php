<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'check_in' => 'sometimes|date_format:Y-m-d H:i:s',
            'check_out' => 'sometimes|date_format:Y-m-d H:i:s|after:check_in',
            'notes' => 'sometimes|string|max:500',
        ];
    }
}

?>