<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'konfirmasi_password' => ['required', 'same:password_baru'],
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.required' => 'Password lama wajib diisi',
            'password_baru.required' => 'Password baru wajib diisi',
            'password_baru.min' => 'Password baru minimal 8 karakter',
            'password_baru.regex' => 'Password baru harus terdiri dari huruf dan angka',
            'konfirmasi_password.required' => 'Konfirmasi password wajib diisi',
            'konfirmasi_password.same' => 'Konfirmasi password tidak cocok',
        ];
    }
}