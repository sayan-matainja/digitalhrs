<?php

namespace App\Requests\TeamMeeting;

use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TeamMeetingRequest extends FormRequest
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
        $this->merge([
            'meeting_start_time' => date('H:i',strtotime($this->meeting_start_time)),
            'meeting_date' => (AppHelper::ifDateInBsEnabled()) ? AppHelper::dateInYmdFormatNepToEng($this->meeting_date) : $this->meeting_date,
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
        return [
            'branch_id' => 'required|exists:branches,id',
            'title' => 'required|string',
            'description' => 'required|string|min:10',
            'venue' => 'required|string|min:3',
            'department' => 'required|array|min:1',
            'department.*.department_id' => 'required|exists:departments,id',
            'participator' => 'required|array|min:1',
            'participator.*.meeting_participator_id' => 'required|exists:users,id',
            'meeting_date' => 'required|date|after_or_equal:today',
            'meeting_start_time' => 'required|date_format:H:i',
            'image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,svg|max:3048'],
            'notification'=> 'nullable',
        ];
    }
}
