<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'The name is required.',
            'email.required'    => 'The email is required.',
            'email.email'       => 'The email must be a valid email address.',
            'email.unique'      => 'This email is already taken.',
            'password.required' => 'The password is required.',
            'password.min'      => 'The password must be at least 6 characters.',
            'password.confirmed'=> 'The password confirmation does not match.',
        ];
    }
}
