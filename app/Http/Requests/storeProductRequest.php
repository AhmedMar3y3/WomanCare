<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeProductRequest extends FormRequest
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
        'name' => 'required|string|max:30',
        'price' => 'required|numeric',
        'description' => 'nullable|string',
        'brand_name' => 'required|string|max:30',
        'discounted' => 'nullable|boolean',
        'category_name' => 'required|string|max:30',
           
        ];
    }
}
