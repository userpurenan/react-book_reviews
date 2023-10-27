<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required'],
            'password' => ['required','min:5', 'max:15'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response  = $validator->errors()->toArray();
    
        throw new HttpResponseException(response()->json([ 'error' => 'バリデーションエラー',
                                                           'detail' => $response], 422));        
    }
}
