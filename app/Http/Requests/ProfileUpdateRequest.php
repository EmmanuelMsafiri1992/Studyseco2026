<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        
        return [
            'name' => [
                'sometimes',  // Only validate if present
                'required',
                'string', 
                'max:255'
            ],
            'email' => [
                'sometimes',  // Only validate if present
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
    
    protected function prepareForValidation()
    {
        $user = $this->user();
        
        // If name or email are missing, fill them from the current user
        if (!$this->has('name') || empty($this->input('name'))) {
            $this->merge(['name' => $user->name]);
        }
        
        if (!$this->has('email') || empty($this->input('email'))) {
            $this->merge(['email' => $user->email]);
        }
    }
}
