<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonationChartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'campaign_id'    => 'sometimes|nullable|integer',
            'tanggal_mulai'  => 'sometimes|nullable|date',
            'tanggal_selesai'=> 'sometimes|nullable|date|after_or_equal:tanggal_mulai',
        ];
    }
}
