<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGuestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Authorization is handled by WeddingPolicy (owner-only access
     * to the dashboard route that invokes this request).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * - name: required, string, max 255 characters
     * - phone_number: optional, string, max 20 characters
     * - group_name: optional, string, max 100 characters (e.g. "Keluarga Besar")
     * - max_pax: required, integer, minimum 1
     * - notes: optional, string, max 1000 characters
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'phone_number'  => ['nullable', 'string', 'max:20'],
            'group_name'    => ['nullable', 'string', 'max:100'],
            'max_pax'       => ['required', 'integer', 'min:1'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages in Indonesian.
     *
     * These messages are displayed to the wedding owner on the dashboard.
     */
    public function messages(): array
    {
        return [
            'name.required'         => 'Nama tamu wajib diisi.',
            'name.string'           => 'Nama tamu harus berupa teks.',
            'name.max'              => 'Nama tamu maksimal 255 karakter.',
            'phone_number.string'   => 'Nomor telepon harus berupa teks.',
            'phone_number.max'      => 'Nomor telepon maksimal 20 karakter.',
            'group_name.string'     => 'Nama grup harus berupa teks.',
            'group_name.max'        => 'Nama grup maksimal 100 karakter.',
            'max_pax.required'      => 'Jumlah kuota tamu wajib diisi.',
            'max_pax.integer'       => 'Jumlah kuota tamu harus berupa angka.',
            'max_pax.min'           => 'Jumlah kuota tamu minimal 1.',
            'notes.string'          => 'Catatan harus berupa teks.',
            'notes.max'             => 'Catatan maksimal 1000 karakter.',
        ];
    }

    /**
     * Get custom attribute names in Indonesian.
     */
    public function attributes(): array
    {
        return [
            'name'          => 'nama tamu',
            'phone_number'  => 'nomor telepon',
            'group_name'    => 'nama grup',
            'max_pax'       => 'kuota tamu',
            'notes'         => 'catatan',
        ];
    }
}
