<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class TokenCacheProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'auth_url' => 'required|string', #Todo: make regex validation for query-strings
            'token_url' => 'required|string', #Todo: make regex validation for query-strings
            'auth_endpoint' => 'required|active_url',
            'client' => 'required|json'
        ];
    }
}
