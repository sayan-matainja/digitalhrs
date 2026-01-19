<?php

namespace App\Requests\Tada;

use Illuminate\Foundation\Http\FormRequest;

class TadaAttachmentRequest extends FormRequest
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

}
