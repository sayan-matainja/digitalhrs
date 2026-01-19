<?php

namespace App\Requests\ThemeColor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeColorRequest extends FormRequest
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
        return [
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'hover_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_hover_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'primary_color.regex' => 'The primary color must be a valid hex color code (e.g., #FFF or #FFFFFF)',
            'hover_color.regex' => 'The hover color must be a valid hex color code (e.g., #FFF or #FFFFFF)',
            'dark_primary_color.regex' => 'The dark primary color must be a valid hex color code (e.g., #FFF or #FFFFFF)',
            'dark_hover_color.regex' => 'The dark hover color must be a valid hex color code (e.g., #FFF or #FFFFFF)',
        ];
    }
}
