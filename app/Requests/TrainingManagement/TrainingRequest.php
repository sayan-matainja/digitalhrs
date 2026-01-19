<?php

namespace App\Requests\TrainingManagement;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingRequest extends FormRequest
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

        $startDate = AppHelper::getEnglishDate($this->input('start_date'));
        $fromDate = Carbon::createFromFormat('Y-m-d', $startDate);

        $data = [
            'start_date' => $fromDate->format('Y-m-d'),
        ];


        if ($this->filled('end_date')) {
            $endDate = AppHelper::getEnglishDate($this->input('end_date'));
            $toDate = Carbon::createFromFormat('Y-m-d', $endDate);

            $data['end_date'] = $toDate->format('Y-m-d');
        }

        $this->merge($data);

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
            'training_type_id' => ['required','exists:training_types,id'],
            'branch_id' => ['required','exists:branches,id'],
            'department_id' => 'required|array|min:1',
            'department_id.*' => 'required|exists:departments,id',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required|exists:users,id',
            'trainer_type' => ['required','array',Rule::in(array_column(TrainerTypeEnum::cases(), 'value')),'min:1'],
            'trainer_type.*' => ['required',Rule::in(array_column(TrainerTypeEnum::cases(), 'value'))],
            'trainer_id' => 'required|array|min:1',
            'trainer_id.*' => 'required|exists:trainers,id',
            'cost' => ['nullable','gt:0'],
            'end_date' => ['nullable','date','date_format:Y-m-d','after_or_equal:start_date'],
            'start_time' => ['required'],
            'certificate' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,svg|max:3048'],
            'description' => ['nullable'],
            'status'=>['nullable'],
            'notification'=> 'nullable',
            'venue'=> 'required|string',
        ];

        if($this->isMethod('put')) {
            $rules['start_date'] = ['required','date','date_format:Y-m-d'];
        } else {
            $rules['start_date'] = ['required','date','date_format:Y-m-d','after_or_equal:today'];
        }

        $rules['end_time'] = ['required'];

        if ($this->input('end_date') == $this->input('start_date') || !$this->input('end_date')) {
            $rules['end_time'][] = 'after:start_time';
        }


        return $rules;
    }
    public function messages()
    {
        return [
            'end_time.after' => 'The end time must be after start time.',

        ];
    }




}

