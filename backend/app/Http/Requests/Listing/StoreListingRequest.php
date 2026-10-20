<?php

namespace App\Http\Requests\Listing;

use App\Models\Listing;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreListingRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image_id' => ['required', 'integer', 'exists:images,id'],
            'coin_purchase_available' => ['sometimes', 'boolean'],
            'trade_available' => ['sometimes', 'boolean'],
            'price' => ['nullable', 'required_unless:coin_purchase_available,false,0', 'integer', 'min:1', 'max:'.Listing::MAX_PRICE],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->boolean('coin_purchase_available', true) && ! $this->boolean('trade_available')) {
                    $validator->errors()->add('coin_purchase_available', 'The listing must allow buying for coins, trading, or both.');
                }
            },
        ];
    }
}
