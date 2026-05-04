<?php

namespace Sitedigitalweb\Pagina\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'page'                 => ['required', 'string', 'max:50'],
            'slug'                 => ['required', 'string', 'max:255'],
            'title'                => ['required', 'string', 'max:55'],
            'keywords'             => ['nullable', 'string', 'max:150'],
            'description'          => ['nullable', 'string', 'max:159'],
            'menu_type'            => ['required', Rule::in(['1', '2'])],
            'position'             => ['required', 'integer', 'min:1'],
            'visibility'           => ['required', Rule::in(['0', '1'])],
            'visibility_ecommerce' => ['required', Rule::in(['0', '1'])],
            'visibility_blog'      => ['required', Rule::in(['0', '1'])],
            'language'             => ['required', Rule::in(['ne', 'es', 'en', 'fr'])],
            'follow'               => ['nullable', 'string'],
            'pixel'                => ['nullable', 'string'],
            'page_id'              => ['nullable', 'integer'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $pageId          = $this->route('id') ?? $this->route('page') ?? null;
            $rules['page'][] = Rule::unique('cms_pages', 'page')->ignore($pageId, 'id');
            $rules['slug'][] = Rule::unique('cms_pages', 'slug')->ignore($pageId, 'id');
        } else {
            $rules['page'][] = Rule::unique('cms_pages', 'page');
            $rules['slug'][] = Rule::unique('cms_pages', 'slug');
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'page.unique' => 'Ya existe una página con ese nombre.',
            'slug.unique' => 'Ya existe una página con esa URL.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $slug = $this->input('slug');
            $this->merge(['slug' => $slug === '/' ? '/' : trim($slug)]);
        }
    }
}
