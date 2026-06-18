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
            'nama_tampil'       => 'sometimes|nullable|string|max:100',
            'pesan'             => 'sometimes|nullable|string|max:500',
        ];
    }
}
