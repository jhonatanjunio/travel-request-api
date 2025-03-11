<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome não pode ter mais que 255 caracteres',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'Digite um e-mail válido',
            'email.unique' => 'Este e-mail já está em uso',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres',
            'password.confirmed' => 'As senhas não conferem',
            'password_confirmation.required' => 'A confirmação de senha é obrigatória',
        ];
    }

    /**
     * Handle a failed validation attempt for API requests.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();

        if ($errors->has('password_confirmation') || $errors->has('password.confirmed')) {
            $errors->forget('password_confirmation');
            $errors->forget('password.confirmed');
            
            if (!$this->has('password_confirmation') || $this->input('password_confirmation') === null) {
                $errors->add('password_confirmation', 'A confirmação de senha é obrigatória');
            } else if ($this->has('password') && $this->input('password') !== $this->input('password_confirmation')) {
                $errors->add('password_confirmation', 'As senhas não conferem');
            }
        }
        
        throw new \Illuminate\Validation\ValidationException($validator, 
            response()->json([
                'message' => 'Os dados fornecidos são inválidos.',
                'errors' => $errors
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if (!$this->has('password_confirmation')) {
            $this->merge(['password_confirmation' => null]);
        }
    }
}