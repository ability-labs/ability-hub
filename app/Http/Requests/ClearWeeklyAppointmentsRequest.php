<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClearWeeklyAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week_start' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
