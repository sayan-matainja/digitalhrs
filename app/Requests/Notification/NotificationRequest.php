<?php

namespace App\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationRequest extends FormRequest
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
            'title' => 'required|string',
            'description' => 'required|string|min:10',
            'company_id' => 'required|exists:companies,id',
            'notification_publish_date' => 'nullable|date|after_or_equal:today',
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

        return $rules;
    }

}
















