<?php

namespace App\Requests\Event;

use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class EventRequest extends FormRequest
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
            'branch_id' => ['required','exists:branches,id'],
            'title' => 'required|string',
            'description' => 'required|string|min:10',
            'location' => 'required|string|min:3',
            'end_date' => ['nullable','date','date_format:Y-m-d','after_or_equal:start_date'],
            'department_id' => 'required|array|min:1',
            'department_id.*' => 'required|exists:departments,id',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required|exists:users,id',
            'attachment' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,svg|max:3048'],
            'start_time' => ['required'],
            'background_color'=>['nullable'],
            'host'=>['required'],
            'notification'=>['nullable'],
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
