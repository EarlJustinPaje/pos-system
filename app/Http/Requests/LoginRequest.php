<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class LoginRequest extends FormRequest
{
    /**
     * Allow all users to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define validation rules for login.
     */
    public function rules(): array
    {
        return [
            'username' => 'required',  // Can be email or username
            'password' => 'required',
        ];
    }

    /**
     * Get the login credentials (supports username or email).
     */
    public function getCredentials(): array
    {
        $username = $this->get('username');

        if ($this->isEmail($username)) {
            return [
                'email' => $username,
                'password' => $this->get('password'),
            ];
        }

        return $this->only('username', 'password');
    }

    /**
     * Check if the provided username is an email.
     */
    private function isEmail(string $param): bool
    {
        $factory = $this->container->make(ValidationFactory::class);

        return !$factory->make(
            ['username' => $param],
            ['username' => 'email']
        )->fails();
    }
}
