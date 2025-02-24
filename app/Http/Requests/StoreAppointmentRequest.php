<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class   StoreAppointmentRequest extends FormRequest
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
            'operator_id'   => 'required|uuid|exists:operators,id',
            'learner_id'    => 'required|uuid|exists:learners,id',
            'discipline_id' => 'required|uuid|exists:disciplines,id',
            'starts_at'    => 'required|date',
            'ends_at'   => 'required|date|after:start_time',
            'comments'     => 'string|nullable|max:2048'
        ];
    }
}
