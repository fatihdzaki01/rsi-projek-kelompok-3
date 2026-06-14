<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nomor_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tanggal_lahir' => ['nullable', 'date'],
            'kode_wilayah' => ['nullable', 'string', 'max:20', 'exists:wilayah,kode'],
        ];
    }

    public function messages(): array
    {
        return [
            'foto_profil.image' => 'File foto profil harus berupa gambar',
            'foto_profil.mimes' => 'Format foto profil harus JPG, JPEG, atau PNG',
            'foto_profil.max' => 'Ukuran foto profil melebihi batas maksimum 2MB',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid',
            'kode_wilayah.exists' => 'Kode wilayah tidak ditemukan',
        ];
    }
}