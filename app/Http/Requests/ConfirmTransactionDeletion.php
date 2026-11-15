<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmTransactionDeletion extends FormRequest
{
    public const PHRASE = 'saya ingin hapus item ini';

    public function rules(): array
    {
        return [
            'konfirmasi_hapus' => ['required', 'string', Rule::in([self::PHRASE])],
        ];
    }

    public function messages(): array
    {
        return [
            'konfirmasi_hapus' => 'Ketik "' . self::PHRASE . '" untuk menghapus transaksi.',
        ];
    }
}
