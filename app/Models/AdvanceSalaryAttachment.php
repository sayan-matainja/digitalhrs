<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceSalaryAttachment extends Model
{
    use HasFactory;

    protected $table = 'advance_salary_attachments';

    public $timestamps = false;

    protected $fillable = [
        'advance_salary_id',
        'name',
    ];

    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/advanceSalary/';

    public function advanceSalaryDetail(): BelongsTo
    {
        return $this->belongsTo(AdvanceSalary::class, 'advance_salary_id', 'id');
    }
}
