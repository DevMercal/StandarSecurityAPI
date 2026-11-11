<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\NotUserPassword;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Password;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');
        $user = User::find($userId);
        $method = $this->method();

        if ($method === 'PUT') {
            return [
                'cedula' => 'required|integer|digits_between:7,8|exists:users,cedula',
                'name' => 'required|string|max:255',
                'username' => [
                            'sometimes',
                            'string',
                            'regex:/^[a-z]+$/u',
                        ],
                'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
                'password' => [
                        'nullable',
                        'confirmed',
                        'min:8',
                        'max:16',
                        Password::min(8)
                            ->letters()
                            ->mixedCase() 
                            ->numbers()  
                            ->symbols(),
                            new NotUserPassword($user),  
                    ],
                'role' => 'required|string',
                'timeToken' => 'required|integer',
                'expiryMonth' => 'required|integer',
            ];
        }else {
            return [
                'cedula' => 'sometimes|integer|min:7,max:8',
                'name' => 'sometimes|string|max:255',
                'username' => [
                            'sometimes',
                            'string',
                            'regex:/^[a-z]+$/u',
                        ],
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
                'password' => [
                        'nullable',
                        'confirmed',
                        'min:8',
                        'max:16',
                        Password::min(8)
                            ->letters()
                            ->mixedCase() 
                            ->numbers()  
                            ->symbols(),
                            new NotUserPassword($user),  
                    ],
                'role' => 'sometimes|string',
                'timeToken' => 'required|integer',
                'expiryMonth' => 'required|integer',
            ];
        }
    }
}
