<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertTransactionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transactions' => 'required|array',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.currency' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ];
    }

    public function messages(): array
    {
        return [
            'transactions.required' => 'A lista de transações é obrigatória.',
            'transactions.array' => 'As transações devem estar em formato de lista.',
            'transactions.*.amount.required' => 'O campo amount é obrigatório em cada transação.',
            'transactions.*.amount.numeric' => 'O campo amount deve ser um número.',
            'transactions.*.currency.required' => 'O campo currency é obrigatório em cada transação.',
            'transactions.*.currency.size' => 'A moeda deve ter exatamente 3 letras (ex: USD, BRL).',
            'to.required' => 'A moeda de destino é obrigatória.',
            'to.size' => 'A moeda de destino deve ter exatamente 3 letras.',
        ];
    }
}
