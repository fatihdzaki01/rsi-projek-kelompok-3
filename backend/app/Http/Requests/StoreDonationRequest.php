<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id_campaign'       => 'required|integer',
            'nominal'           => 'required|integer|min:5000',
            'metode_pembayaran' => 'required|string|in:qris,gopay,ovo,shopeepay,bca,mandiri,bni',
            'is_anonim'         => 'sometimes|boolean',
            'nama_tampil'       => 'required_if:is_anonim,false,0|nullable|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'pesan'             => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_tampil.required_if' => 'Nama tampil wajib diisi jika tidak anonim',
            'nama_tampil.regex'       => 'Nama tampil tidak boleh hanya berisi angka',
        ];
    }
}
