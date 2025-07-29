<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LunchSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }
}

?>