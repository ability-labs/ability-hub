<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DuplicateWeeklyAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week_start' => ['required', 'date_format:Y-m-d'],
            'week_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:week_start'],
        ];
    }
}
