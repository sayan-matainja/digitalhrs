<?php

namespace App\Requests\Tada;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TadaRequest extends FormRequest
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
        if($this->route()->getPrefix() === 'api'){
            $this->merge([
                'employee_id' => getAuthUserCode(),
                'branch_id' => auth()->user()?->branch_id,
                'department_id' => auth()->user()?->department_id,
            ]);
        }
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
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'title' => ['required','string','max:400'],
            'employee_id' => ['required',
                Rule::exists('users','id')
                    ->where('is_active',1)
                    ->where('status','verified')
            ],
            'total_expense' => ['required','numeric','digits_between:1,7'],
            'description' => ['nullable','string'],
        ];
        if($this->route()->getPrefix() === 'api'){
            if(isset($this->tada_id) ){
                $rules['attachments'] = ['sometimes','array','min:1'];
                $rules['attachments.*.'] = ['sometimes','file','mimes:jpeg,png,jpg,docx,doc,xls,pdf','max:5048'];
            }else{
                $rules['attachments'] = ['nullable','array','min:1'];
                $rules['attachments.*.'] = ['nullable','file','mimes:jpeg,png,jpg,docx,doc,xls,pdf','max:5048'];
            }
        }else{
            if($this->isMethod('put') ){
                $rules['attachments'] = ['sometimes','array','min:1'];
                $rules['attachments.*.'] = ['sometimes','file','mimes:jpeg,png,jpg,docx,doc,xls,pdf','max:5048'];
            }else{
                $rules['attachments'] = ['nullable','array','min:1'];
                $rules['attachments.*.'] = ['nullable','file','mimes:jpeg,png,jpg,docx,doc,xls,pdf','max:5048'];
            }
        }

        return $rules;
    }
}
