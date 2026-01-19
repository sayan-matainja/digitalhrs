<?php

namespace App\Requests\Notice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoticeRequest extends FormRequest
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

    public function prepareForValidation()
    {

        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'receiver.department.*' => 'sometimes|exists:departments,id',
            'receiver.employee.*' => 'sometimes|exists:users,id',
            'receiver' => 'required|array',
            'receiver.department' => 'required_without:receiver.employee|array',
            'receiver.employee' => 'required_without:receiver.department|array',
            'is_active' => 'required|in:0,1',
            'notification'=> 'nullable',
        ];
    }
}
