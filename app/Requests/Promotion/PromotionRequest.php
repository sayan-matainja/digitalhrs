<?php

namespace App\Requests\Promotion;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
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

        $startDate = AppHelper::getEnglishDate($this->input('promotion_date'));
        $fromDate = Carbon::createFromFormat('Y-m-d', $startDate);

        $this->merge([
            'promotion_date' => $fromDate->format('Y-m-d'),
        ]);
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
            'branch_id' => ['required','exists:branches,id'],
            'department_id' => ['required','exists:departments,id'],
            'employee_id' => ['required','exists:users,id'],
            'post_id' => ['required','exists:posts,id'],
            'old_post_id' => ['nullable'],
            'description' => ['nullable'],
            'notification'=> 'nullable',
        ];

        if($this->isMethod('put')) {
            $rules['promotion_date'] = ['required','date','date_format:Y-m-d'];
        } else {
            $rules['promotion_date'] = ['required','date','date_format:Y-m-d','after_or_equal:today'];
        }


        return $rules;
    }




}

