<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'category_id' => ['required', 'exists:categories,id'],
            'author_ids' => ['array'],
            'author_ids.*' => ['exists:authors,id'],
            'description' => ['nullable', 'string'],
            'isbn' => ['nullable', 'string', 'max:32', Rule::unique('products', 'isbn')->ignore($productId)],
            'publisher' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'active' => ['boolean'],
            'featured' => ['boolean'],
        ];
    }
}
