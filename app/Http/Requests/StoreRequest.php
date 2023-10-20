<?php

namespace App\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'storeId' => ['required', 'max:50'],
            'domain' => ['required', 'max:100', 'regex:/(.*)' . preg_quote(Store::SHOPIFY_DOMAIN) . '$/i'],
        ];
    }
}
