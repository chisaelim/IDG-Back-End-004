<?php

namespace App\Http\Requests\ChatMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(['admin', 'member'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role is required',
            'role.in' => 'Role must be either admin or member',
        ];
    }
}
