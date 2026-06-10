<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status_pembayaran'    => 'required|string|in:berhasil,gagal',
            'catatan_verifikasi'   => 'sometimes|nullable|string|max:255',
        ];
    }
}
