<?php

namespace App\Requests\ContentManagement;


use App\Models\CompanyContentManagement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentManagementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'content_type' => ['required', 'string', Rule::in(CompanyContentManagement::CONTENT_TYPE)],
            'description' => 'required|string|min:10',
            'company_id' => 'required|exists:companies,id',
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
        $rules['title'] = ['required','string',Rule::unique('company_content_management')->ignore($this->static_page_content)];

        return $rules;
    }
}
