<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize():bool
    {
        return true;
    }

    public function rules():array{
        return [
            'title'=>'required|string|min:3|max:255|unique:books,title,'.$this->route('book')->id,
            'author'=>'required|string|min:3|max:255',
            'publisher'=>'required|string|min:3|max:255',
            'isbn'=>'required|string|min:3|max:255|unique:books,isbn,'.$this->route('book')->id,
            'description'=>'required|string|min:3|max:255'
        ]
    }
}

?>