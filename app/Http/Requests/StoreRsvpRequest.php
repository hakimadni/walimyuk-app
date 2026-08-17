<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRsvpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Authorization is handled by the ValidateGuestToken middleware,
     * so we allow all requests that reach this point.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * - attendance_status: required, must be one of the enum values
     * - pax_count: required when attending, must be integer >= 1
     *   (max pax is validated in RsvpService against guest.max_pax)
     * - comment: optional, string, max 1000 characters
     */
    public function rules(): array
    {
        return [
            'attendance_status' => ['required', 'string', 'in:attending,not_attending,maybe'],
            'pax_count'         => ['required_if:attendance_status,attending', 'integer', 'min:1'],
            'comment'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages in Indonesian.
     *
     * These messages are displayed to public/guest users on the invitation page.
     */
    public function messages(): array
    {
        return [
            'attendance_status.required'  => 'Status kehadiran wajib diisi.',
            'attendance_status.string'    => 'Status kehadiran tidak valid.',
            'attendance_status.in'        => 'Status kehadiran harus salah satu dari: hadir, tidak hadir, mungkin.',
            'pax_count.required_if'       => 'Jumlah tamu wajib diisi jika Anda hadir.',
            'pax_count.integer'           => 'Jumlah tamu harus berupa angka.',
            'pax_count.min'               => 'Jumlah tamu minimal 1 orang.',
            'comment.string'              => 'Komentar harus berupa teks.',
            'comment.max'                 => 'Komentar maksimal 1000 karakter.',
        ];
    }

    /**
     * Get custom attribute names in Indonesian.
     */
    public function attributes(): array
    {
        return [
            'attendance_status' => 'status kehadiran',
            'pax_count'         => 'jumlah tamu',
            'comment'           => 'komentar',
        ];
    }
}
