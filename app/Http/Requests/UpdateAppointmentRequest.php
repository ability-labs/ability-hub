<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
//            'title'         => 'required|string|max:255',
            'operator_id'   => 'sometimes|required_without:operator_ids|uuid|exists:operators,id',
            'learner_id'    => 'sometimes|required_without:learner_ids|uuid|exists:learners,id',
            'operator_ids'  => 'sometimes|required_without:operator_id|array|min:1',
            'operator_ids.*'=> 'uuid|exists:operators,id',
            'learner_ids'   => 'sometimes|required_without:learner_id|array|min:1',
            'learner_ids.*' => 'uuid|exists:learners,id',
            'discipline_id' => 'required|uuid|exists:disciplines,id',
            'starts_at'     => 'required|date',
            'ends_at'       => 'required|date|after:starts_at',
            'comments'      => 'string|nullable|max:2048'
        ];
    }

    protected function prepareForValidation(): void
    {
        $timezone = config('app.timezone');

        foreach (['starts_at', 'ends_at'] as $field) {
            $value = $this->input($field);

            if (!$value) {
                continue;
            }

            try {
                $this->merge([
                    $field => Carbon::parse($value)->setTimezone($timezone)->toDateTimeString(),
                ]);
            } catch (\Throwable $e) {
                // Ignore parsing issues and let the validator handle the error
            }
        }
    }
}
