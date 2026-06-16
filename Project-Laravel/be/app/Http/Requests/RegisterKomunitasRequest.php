<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterKomunitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'nama_lembaga'   => ['required', 'string', 'max:255'],
            'id_jenis_lembaga' => ['required', 'integer', 'exists:jenis_lembaga,id_jenis'],
            'kode_wilayah'   => ['required', 'string', 'exists:wilayah,kode'],
            'alamat_detail'  => ['required', 'string', 'max:500'],
            'nomor_kontak'   => ['required', 'string', 'max:20'],
            'deskripsi'      => ['nullable', 'string'],
            'link_medsos'    => ['nullable', 'string', 'max:255'],
            'foto_lembaga_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'       => 'Email wajib diisi',
            'email.email'          => 'Format email tidak valid',
            'email.unique'         => 'Email sudah digunakan',
            'password.required'    => 'Password wajib diisi',
            'password.min'         => 'Password minimal 8 karakter',
            'password.regex'       => 'Password harus terdiri dari huruf dan angka',
            'nama_lembaga.required' => 'Nama lembaga wajib diisi',
            'id_jenis_lembaga.required' => 'Jenis lembaga wajib dipilih',
            'id_jenis_lembaga.exists' => 'Jenis lembaga tidak valid',
            'kode_wilayah.required' => 'Wilayah wajib dipilih',
            'kode_wilayah.exists'  => 'Wilayah tidak valid',
            'alamat_detail.required' => 'Alamat wajib diisi',
            'nomor_kontak.required' => 'Nomor kontak wajib diisi',
        ];
    }
}
