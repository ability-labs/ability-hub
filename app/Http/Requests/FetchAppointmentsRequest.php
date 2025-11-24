<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class FetchAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'starts_at' => ['nullable', 'date_format:Y-m-d'],
            'ends_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_at'],
        ];
    }

    public function startDate(): Carbon
    {
        $value = $this->validated()['starts_at'] ?? null;

        if ($value) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        return now()->startOfMonth();
    }

    public function endDate(): Carbon
    {
        $value = $this->validated()['ends_at'] ?? null;

        if ($value) {
            return Carbon::createFromFormat('Y-m-d', $value)->endOfDay();
        }

        return now()->endOfMonth();
    }
}
