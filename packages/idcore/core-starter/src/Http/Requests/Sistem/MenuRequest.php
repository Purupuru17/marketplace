<?php

namespace IdCore\CoreStarter\Http\Requests\Sistem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Mendapatkan ID menu yang sedang di-update (jika ada) untuk kebutuhan ignore rules jika diperlukan kelak
        $menuId = $this->route('menu')?->id;

        return [
            'name'            => 'required|string|max:100',
            'url'             => 'nullable|string|max:150',
            'icon'            => 'nullable|string|max:100',
            'actions'         => 'nullable|array',
            'actions.*'       => 'in:' . implode(',', array_keys(config('idcore.menu_actions'))),
            'parent_id'       => 'nullable|exists:menus,id',
            'sort_by'         => 'nullable|integer|min:0',
            'is_active'       => 'boolean',
        ];
    }

    /**
     * Opsional: Kustomisasi pesan error bahasa Indonesia agar lebih rapi.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi.',
            'parent_id.exists' => 'Menu parent yang dipilih tidak valid.',
        ];
    }
}
