<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LunchRequestRequest extends FormRequest
{
    public function authorize()
    {
          return auth()->check();
    }

    public function rules()
    {
        return [
            'has_lunch' => 'required|boolean',
            'date' => [
                'sometimes',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . now()->addDays(30)->toDateString()
            ],
        ];
    }
     public function messages()
    {
        return [
            'date.after_or_equal' => 'Cannot submit lunch request for past dates.',
            'date.before_or_equal' => 'Cannot submit lunch request more than 30 days in advance.',
            'has_lunch.required' => 'Please specify whether you want lunch or not.',
            'has_lunch.boolean' => 'Lunch preference must be true or false.',
        ];
    }
}

?>