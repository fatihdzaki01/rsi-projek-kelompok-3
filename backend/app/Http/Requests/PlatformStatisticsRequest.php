<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlatformStatisticsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tanggal_mulai'  => 'sometimes|nullable|date',
            'tanggal_selesai'=> 'sometimes|nullable|date|after_or_equal:tanggal_mulai',
        ];
    }
}
